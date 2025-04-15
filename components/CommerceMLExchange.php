<?php

namespace app\components;

use app\models\Category;
use app\models\Product;
use app\models\Shop;
use Yii;
use yii\base\Component;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\base\Exception;

class CommerceMLExchange extends Component
{
    public $login;
    public $password;
    public $tempPath = '@runtime/1c-exchange';
    public $useZip = true;
    private $shop_id = null;
    private $categories = array();

    /**
     * Обработка запроса от 1С
     * @return Response
     * @throws UnauthorizedHttpException
     */
    public function handleRequest()
    {
        if (!$this->checkAuth()) {
            throw new UnauthorizedHttpException('Неверные учетные данные');
        }

        $request = Yii::$app->request;
        $mode = $request->get('mode');
        $type = $request->get('type');

        switch ($mode) {
            case 'checkauth':
                return $this->handleCheckAuth();

            case 'init':
                return $this->handleInit();

            case 'file':
                return $this->handleFile($type);

            case 'import':
                return $this->handleImport($type);

            default:
                return $this->sendResponse(false, 'Неизвестный режим обмена');
        }
    }

    /**
     * Проверка авторизации
     * @return bool
     */
    protected function checkAuth()
    {
        $authUserStr = Yii::$app->request->getAuthUser();

        $authUserData = explode(';;;', $authUserStr);
        $authUser = !empty($authUserData[0]) ? $authUserData[0] : null;
        if(!empty($authUserData[1])){
            $shopData = Shop::findOne(['identifier'=>$authUserData[1]]);
            if($shopData){
                $this->shop_id = $shopData->getAttribute('id');
            }
        }

        $authPassword = Yii::$app->request->getAuthPassword();

        return $authUser === $this->login && $authPassword === $this->password && $this->shop_id !== null;
    }

    /**
     * Обработка этапа проверки авторизации
     * @return Response
     */
    protected function handleCheckAuth()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->content = "success\nsession_id\n" . time();
        return $response;
    }

    /**
     * Обработка этапа инициализации
     * @return Response
     */
    protected function handleInit()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->content = "zip=" . ($this->useZip ? 'yes' : 'no') . "\nfile_limit=1000000";
        return $response;
    }

    /**
     * Обработка получения файла от 1С
     * @param string $type
     * @return Response
     */
    protected function handleFile($type)
    {
        try {
        $input = Yii::$app->request->getRawBody();

        if (empty($input)) {
                throw new Exception('Пустой файл');
        }

        $filename = $this->getFilenameByType($type);
            $filePath = Yii::getAlias($this->tempPath) . '/' . $filename;

            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            file_put_contents($filePath, $input);

            // Если файл архивный - распаковываем
            if ($this->useZip && $this->isZipFile($filePath)) {
                $this->extractZip($filePath, dirname($filePath));
                unlink($filePath); // Удаляем архив после распаковки
            }

            return $this->sendResponse(true, 'Файл успешно получен');

        } catch (Exception $e) {
            Yii::error('Ошибка при получении файла: ' . $e->getMessage(), '1c-exchange');
            return $this->sendResponse(false, $e->getMessage());
        }
    }

    /**
     * Проверка, является ли файл ZIP-архивом
     * @param string $filePath
     * @return bool
     */
    protected function isZipFile($filePath)
    {
        return mime_content_type($filePath) === 'application/zip';
    }

    /**
     * Распаковка ZIP-архива
     * @param string $zipPath
     * @param string $extractTo
     * @throws Exception
     */
    protected function extractZip($zipPath, $extractTo)
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new Exception('Не удалось открыть ZIP-архив');
        }

        if (!$zip->extractTo($extractTo)) {
            $zip->close();
            throw new Exception('Не удалось распаковать архив');
        }

        $zip->close();
    }

    /**
     * Обработка импорта данных
     * @param string $type
     * @return Response
     */
    protected function handleImport($type)
    {
        //Если в процессе импорта будет ошибка то откатим до состояния начала импорта.
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $dirPath = Yii::getAlias($this->tempPath);

            // Находим все XML файлы в директории
            $xmlFiles = $this->findImportXmlFiles($dirPath);

            //Если нет файла импорта, то нет смысла обрабатывать предложения
            if (!is_array($xmlFiles['import']) || empty($xmlFiles['import'])) {
                throw new Exception('XML файлы не найдены');
            }

            $this->markAsDeletedCategories();
            $this->markAsDeletedProducts();
            $categories = [];
            $products = [];
            $offers = [];
            // Обрабатываем каждый файл каталога
            foreach ($xmlFiles['import'] as $file) {
                $result = $this->processXmlFile($file, $type);
                if(isset($result['categories']) && is_array($result['categories'])){
                    $categories = array_merge($categories, $result['categories']);
                }
                if(isset($result['products']) && is_array($result['products'])){
                    $products = array_merge($products, $result['products']);
                }
            }
            // Обрабатываем каждый файл предложений
            if(isset($xmlFiles['offers']) && is_array($xmlFiles['offers']) && count($xmlFiles['offers'])) {
                foreach ($xmlFiles['offers'] as $file) {
                    $result = $this->processXmlFile($file, $type);
                    if (isset($result['offers']) && is_array($result['offers'])) {
                        $offers = array_merge($offers, $result['offers']);
                    }
                }
            }
            if(is_array($categories) && count($categories)) {
                $this->batchUpsert($categories, new Category());
                $this->categories = Category::find()
                    ->where(['shop_id' => $this->shop_id])
                    ->indexBy('identifier')
                    ->asArray()
                    ->all();
            }

            $productRecords = $this->buildProductRecords($products, $offers);

            if(is_array($productRecords) && count($productRecords)) {
                $this->batchUpsert($productRecords, new Product());
            }

            // Очищаем временные файлы
            $this->cleanTempFiles($dirPath);

            //Всё хорошо, можем коммитить
            $transaction->commit();

            return $this->sendResponse(
                true,
                'Импорт завершен',
                []
            );

        } catch (Exception $e) {
            //Вернём всё как было до начала импорта
            $transaction->rollBack();
            Yii::error('Ошибка импорта: ' . $e->getMessage(), '1c-exchange');
            return $this->sendResponse(false, $e->getMessage());
        }
    }

    /**
     * Собираем записи товаров для вставки в бд
     *
     * @param array $products
     * @param array $offers
     * @return array
     *
     */
    protected function buildProductRecords($products, $offers){
        $records = [];
        if(is_array($products) && count($products)){
            foreach ($products as $product) {
                $price = isset($offers[$product['identifier']]) && isset($offers[$product['identifier']]['price']) ? intval($offers[$product['identifier']]['price']) : 0;
                $category_id = isset($product['category_ident']) && isset($this->categories[$product['category_ident']]) && isset($this->categories[$product['category_ident']]['id']) ? intval($this->categories[$product['category_ident']]['id']) : null;
                $records[] = [
                    'identifier' => $product['identifier'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'article' => $product['article'],
                    'category_id' => $category_id,
                    'image_filename' => $product['image_filename'],
                    'is_deleted' => 0,
                    'shop_id' => $this->shop_id,
                    'price' => $price
                ];
            }
        }
        return $records;
    }

    /**
     * Поиск всех XML файлов для импорта
     */
    protected function findImportXmlFiles($dirPath)
    {
        $files['import'] = glob($dirPath . '/import*.xml');
        $files['offers'] = glob($dirPath . '/offers*.xml');

        if(is_array($files['import'])) {
            $files['import'] = array_unique($files['import']);
        }
        if(is_array($files['offers'])) {
            $files['offers'] = array_unique($files['offers']);
        }

        return $files;
    }

    /**
     * Обработка конкретного XML файла
     */
    protected function processXmlFile($filePath, $type)
    {
        $xml = simplexml_load_file($filePath);
        $filename = basename($filePath);
        $result = [];

        // Определяем тип файла по имени
        if($type == 'catalog'){
            if (strpos($filename, 'import') !== false) {
                $result = $this->processCatalogXml($xml);
            } elseif (strpos($filename, 'offers') !== false) {
                $result = $this->processOffersXml($xml);
            }
        }
        return $result;
    }

    /**
     * Обработка файла каталога (import*.xml)
     */
    protected function processCatalogXml($xml)
    {
        $categories = [];
        $products = [];

        // Обработка групп
        if (isset($xml->Классификатор->Группы)) {
            foreach ($xml->Классификатор->Группы->Группа as $group) {

                $this->processGroup($group, null, $categories);
            }
        }

        // Обработка товаров
        if (isset($xml->Каталог->Товары)) {
            foreach ($xml->Каталог->Товары->Товар as $product) {
                $this->processProduct($product, $products);
            }
        }

        return [
            'categories' => $categories,
            'products' => $products,
        ];
    }

    /**
     * Обработка файла предложений (offers*.xml)
     */
    protected function processOffersXml($xml)
    {
        $offers = [];

        if (isset($xml->ПакетПредложений->Предложения)) {
            foreach ($xml->ПакетПредложений->Предложения->Предложение as $offer) {
                $this->parseOffer($offer, $offers);
            }
        }

        return [
            'offers' => $offers,
        ];
    }

    /**
     * Парсинг группы товаров (категории)
     */
    protected function processGroup($groupNode, $parentId = null, &$categories = array())
    {
        $identifier = (string)$groupNode->Ид;
        $categories[] = [
            'identifier' => $identifier,
            'name' => (string)$groupNode->Наименование,
            'parent_ident' => (string)$parentId,
            'shop_id' => $this->shop_id,
            'is_deleted' => 0
        ];

        if (isset($groupNode->Группы->Группа)) {
            foreach ($groupNode->Группы->Группа as $childGroup) {
                $this->processGroup($childGroup, $identifier, $categories);
            }
        }
    }

    /**
     * Парсинг товара
     */
    protected function processProduct($productNode, &$products = array())
    {
        $identifier = (string)$productNode->Ид;
        $image = null;
        $imagePath = (string)$productNode->Картинка;
        $imageTmpPath = Yii::getAlias($this->tempPath) . DIRECTORY_SEPARATOR . $imagePath;
        if(!empty($imagePath) && is_file($imageTmpPath)){
            $targetPath = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            $image = basename($imagePath);
            if(is_file($targetPath . $image)){
                unlink($targetPath . $image);
            }
            if(!copy($imageTmpPath, $targetPath . $image)){
                $image = null;
            }

        }
        $products[$identifier] = [
            'identifier' => $identifier,
            'name' => (string)$productNode->Наименование,
            'description' => (string)$productNode->Описание,
            'article' => (string)$productNode->Артикул,
            'category_ident' => (string)$productNode->Группы->Ид,
            'image_filename' => $image
        ];
    }

    /**
     * Парсинг предложения
     */
    protected function parseOffer($offerNode, &$offers = array())
    {
        $identifier = (string)$offerNode->Ид;
        $offers[$identifier] = [
            'product_ident' => $identifier,
            'price' => (float)$offerNode->Цены->Цена->ЦенаЗаЕдиницу,
            'quantity' => (int)$offerNode->Количество
        ];
    }

    /**
     * Метод sendResponse для передачи дополнительных данных
     */
    protected function sendResponse($success, $message = '123')
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><КоммерческаяИнформация></КоммерческаяИнформация>');
        $xml->addAttribute('ВерсияСхемы', '2.04');
        $xml->addAttribute('ДатаФормирования', date('Y-m-d H:i:s'));

        $result = $xml->addChild('Результат');
        $result->addChild('Статус', $success ? 'success' : 'failure');
        $result->addChild('Описание', $message);


        $response = Yii::$app->response;
        $response->format = Response::FORMAT_XML;
        $response->data = $xml;

        return $response;
    }

    /**
     * Вспомогательный метод для преобразования массива в XML
     */
    protected function arrayToXml($array, &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item_' . $key;
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * Очистка временных файлов
     * @param string $dirPath
     */
    protected function cleanTempFiles($dirPath)
    {
        $files = glob($dirPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Генерация имени файла
     * @param string $type
     * @return string
     */
    protected function getFilenameByType($type)
    {
        return '1c_' . $type . '_' . date('YmdHis') . ($this->useZip ? '.zip' : '.xml');
    }

    /**
     * Помечаем категории магазина как удалённые
     */
    protected function markAsDeletedCategories()
    {

        Category::updateAll(
            ['is_deleted' => 1],
            ['shop_id' => $this->shop_id]
        );
    }

    /**
     * Помечаем товары магазина как удалённые
     */
    protected function markAsDeletedProducts()
    {
        Product::updateAll(
            ['is_deleted' => 1],
            ['shop_id' => $this->shop_id]
        );
        
    }

    public function batchUpsert(array $inputRecords, \yii\db\ActiveRecord $model, $uniqueKeys = 'identifier')
    {
        $db = Yii::$app->db;
        if (empty($inputRecords)) {
            return ['inserted' => 0, 'updated' => 0];
        }

            // Нормализуем ключи
            if (!is_array($uniqueKeys)) {
                $uniqueKeys = [$uniqueKeys];
            }

            // Получаем все существующие записи одним запросом
            $existingConditions = ['or'];
            foreach ($inputRecords as $record) {
                $andCondition = ['and'];
                foreach ($uniqueKeys as $key) {
                    if (!isset($record[$key])) {
                        throw new \InvalidArgumentException("Key '$key' not found in input record");
                    }
                    $andCondition[] = [$key => $record[$key]];
                }
                $existingConditions[] = $andCondition;
            }

            $existingRecords = $model::find()
                ->where($existingConditions)
                ->indexBy(function ($record) use ($uniqueKeys) {
                    $keyParts = [];
                    foreach ($uniqueKeys as $k) {
                        $keyParts[] = $record->$k;
                    }
                    return implode('|', $keyParts);
                })
                ->all();

            $inserted = 0;
            $updated = 0;
            $batchInsert = [];

            foreach ($inputRecords as $record) {
                $recordKeyParts = [];
                foreach ($uniqueKeys as $k) {
                    $recordKeyParts[] = $record[$k];
                }
                $recordKey = implode('|', $recordKeyParts);

                if (isset($existingRecords[$recordKey])) {
                    // Обновление существующей записи
                    $model = $existingRecords[$recordKey];
                    $needUpdate = false;

                    foreach ($record as $attr => $value) {
                        if (!in_array($attr, $uniqueKeys) && $model->$attr != $value) {
                            $model->$attr = $value;
                            $needUpdate = true;
                        }
                    }

                    if ($needUpdate && $model->save(false)) {
                        $updated++;
                    }
                } else {
                    // Подготовка к пакетной вставке
                    $batchInsert[] = $record;
                }
            }

            // Пакетная вставка новых записей
            if (!empty($batchInsert)) {
                $inserted = $db->createCommand()
                    ->batchInsert($model::tableName(), array_keys($batchInsert[0]), $batchInsert)
                    ->execute();
            }
            
            return ['inserted' => $inserted, 'updated' => $updated];

    }


}