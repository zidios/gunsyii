<?php

namespace app\widgets;

use yii\base\Widget;
use yii\web\Cookie;

class CitySelectorWidget extends Widget
{
    public $cities = ['Белгород', 'Воронеж'];
    public $cookieName = 'usercity';
    public $cookieExpire = 2592000;

    public function run()
    {
        parent::run();

// Проверяем куки города, т.к. куки устанавливаются из JS, валидацию они не пройдут, вынимаем их напрямую
        $city = isset($_COOKIE['usercity']) ? $_COOKIE['usercity']: null;
// Регистрируем JS-код
        $this->registerJs($city);

        return $this->render('citySelector', [
            'city' => $city,
            'cities' => $this->cities
        ]);
    }

    protected function registerJs($currentCity)
    {
        $js = <<<JS
    $(document).ready(function() {
// Проверяем наличие города
var currentCity = '$currentCity';
var cookieName = '{$this->cookieName}';
var cities = JSON.parse('{$this->getCitiesJson()}');

if (currentCity) {
// Устанавливаем выбранный город
$('.city-select .inner-span').text(currentCity);
} else {
// Показываем модальное окно
$('#cityModal').modal('show');
}

$('div.city-select').on('click', function() {
    $('#cityModal').modal('show');
});
// Обработка выбора города
$('.city-btn').on('click', function() {
    var selectedCity = $(this).data('city');
    
    // Устанавливаем куки
    var date = new Date();
    date.setTime(date.getTime() + (24 * 60 * 60 * 1000)); // 1 день
    document.cookie = cookieName + '=' + encodeURIComponent(selectedCity) +
    '; expires=' + date.toUTCString() +
    '; path=/';
    
    // Обновляем отображение города
    let cityText = $('.city-select .inner-span').text()
    if(cityText !== selectedCity){
        window.location.reload();
    }
    $('#cityModal').modal('hide');
});
});
JS;

        $this->view->registerJs($js);
    }

    protected function getCitiesJson()
    {
        return json_encode($this->cities);
    }
}