<?php

namespace app\controllers;
class HomeController extends AppController
{
    public function actionIndex() {
        return $this->render('index');
    }
    public function actionAbout() {
        return $this->render('about');
    }
    public function actionContacts() {
        return $this->render('contacts');
    }
    public function actionPayment() {
        return $this->render('payment');
    }
    public function actionDelivery() {
        return $this->render('delivery');
    }
    public function actionPrivacy() {
        return $this->render('privacy');
    }
    public function actionGiftcards() {
        return $this->render('giftcards');
    }
}