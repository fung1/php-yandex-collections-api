# API сервиса Яндекс.Коллекции
Простенькая обертка для вызовов API сервиса Яндекс.Коллекции (https://yandex.ru/collections/)
  
## Требования  
PHP 7.3+  
  
## Установка
Установка с помощью Composer:  
    composer require krokodilushka/php-yandex-collections-api:dev-master  
  
## Пример
Нужен OAuth токен (https://yandex.ru/dev/collections/doc/concepts/access-docpage/).  
Отладочный токен можно получить таким образом: https://oauth.yandex.ru/authorize?response_type=token&client_id=<APP_ID>.  
Также необходимо указать имя компании, которое можно увидеть в URL после user/: https://yandex.ru/collections/user/company%40your-company/ (здесь имя компании: company@your-company).  
  
    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    
    const OAUTH_TOKEN = 'token';
    // Имя компании, от которой публикуются коллекции
    const COMPANY_NAME = 'company@companyName';

    $httpClient = new \GuzzleHttp\Client();
    $yandexCollectionsAPI = new YandexCollectionsAPI\YandexCollectionsAPI($httpClient, OAUTH_TOKEN, COMPANY_NAME);
    
    try {
        $page = 1; // страница
        $pageSize = 10; // сколько выводить на одной странице. Максимум 100
        $list = $yandexCollectionsAPI->boards()->list($page, $pageSize);
        print_r($list);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        echo "API вернул ошибку:\n";
        echo "http code:" . $e->getCode() . "\n";
        echo $e->getResponse()->getBody();
    }
    ?>  
    
Остальные примеры есть в файле examples.php
