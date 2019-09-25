<?php

/**
 * API сервиса Яндекс.Коллекции
 *
 * @author Krokodilushka
 * @version 0.1
 * @link https://github.com/Krokodilushka/php-yandex-collections-api
 */

namespace YandexCollectionsAPI;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/DataClasses.php';

class YandexCollectionsAPI {

    private $boards;
    private $cards;

    /**
     * Класс для хранения объектов, через которые идет взаимодействие с API Яндекс коллекций
     * 
     * @param \GuzzleHttp\Client $httpClient HTTP клиент для запросов к Яндекс API
     * @param string $token OAuth токен пользователя, от имени которого будут вызываться методы API {@link https://yandex.ru/dev/collections/doc/concepts/access-docpage/}
     */
    public function __construct(\GuzzleHttp\Client $httpClient, string $token) {
        $this->boards = new Boards($httpClient, $token);
        $this->cards = new Cards($httpClient, $token);
    }

    /**
     * 
     * @return \YandexCollectionsAPI\Boards объект для работы с досками {@link https://yandex.ru/dev/collections/doc/ref/Boards-docpage/}
     */
    public function boards(): Boards {
        return $this->boards;
    }

    /**
     * 
     * @return \YandexCollectionsAPI\Cards объект для работы с карточками {@link https://yandex.ru/dev/collections/doc/ref/Cards-docpage/}
     */
    public function cards(): Cards {
        return $this->cards;
    }

}

class Boards extends Base {

    /**
     * Список всех досок
     * 
     * @param int $page Страница
     * @param int $pageSize Сколько элементов на странице
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Boards/v1_boards_get-docpage/}
     */
    public function list(int $page = 1, int $pageSize = 20): \stdClass {
        $params['query'] = ['page' => $page, 'page_size' => $pageSize];
        return $this->query('GET', '/v1/boards/', $params);
    }

    /**
     * Информация о определенной доске
     * 
     * @param string $id ID доски
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Boards/v1_boards_id_get-docpage/}
     */
    public function get(string $id): \stdClass {
        return $this->query('GET', '/v1/boards/' . $id);
    }

    /**
     * Добавить новую доску
     * 
     * @param bool $isPrivate Приватность. true - приватная, false - публичная
     * @param string $title Название доски
     * @param string|null $description Описание доски. null - без описания
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Boards/v1_boards_post-docpage/}
     */
    public function insert(bool $isPrivate, string $title, ?string $description = NULL): \stdClass {
        $params = [
            'is_private' => $isPrivate,
            'title' => $title,
        ];
        if ($description !== NULL) {
            $params['description'] = $description;
        }
        return $this->query('POST', '/v1/boards/', ['json' => $params]);
    }

    /**
     * Изменить существующую доску
     * 
     * @param string $id ID изменяемой доски
     * @param bool|null $isPrivate Приватность. true - приватная, false - публичная. null - не менять
     * @param string|null $title Новое название доски. null - не менять
     * @param string|null $description Новое описание доски. null - не менять
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Boards/v1_boards_id_patch-docpage/}
     */
    public function update(string $id, bool $isPrivate = NULL, ?string $title = NULL, ?string $description = NULL): \stdClass {
        $params = [];
        if ($isPrivate !== NULL) {
            $params['is_private'] = $isPrivate;
        }
        if ($title !== NULL) {
            $params['title'] = $title;
        }
        if ($description !== NULL) {
            $params['description'] = $description;
        }
        return $this->query('PATCH', '/v1/boards/' . $id, ['json' => $params]);
    }

    /**
     * Удаление доски
     * 
     * @param string $id ID удаляемой доски
     * @return bool true в случае успеха. В случае ошибки будет выброшено исключение {@see \GuzzleHttp\Exception\ClientException}
     */
    public function delete(string $id): bool {
        return $this->query('DELETE', '/v1/boards/' . $id);
    }

}

class Cards extends Base {

    /**
     * Получить все карточки доски
     * 
     * @param string $boardID ID доски
     * @param int $page Страница
     * @param int $pageSize Сколько элементов на странице. Максимум 100
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Cards/v1_cards_get-docpage/}
     */
    public function list(string $boardID, int $page = 1, int $pageSize = 20): \stdClass {
        $params['query'] = ['board_id' => $boardID, 'page' => $page, 'page_size' => $pageSize];
        return $this->query('GET', '/v1/cards/', $params);
    }

    /**
     * Информация о определенной карточке
     * 
     * @param string $id ID карточки
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Cards/v1_cards_id_get-docpage/}
     */
    public function get(string $id): \stdClass {
        return $this->query('GET', '/v1/cards/' . $id . '/');
    }

    /**
     * Добавить на доску новую карточку
     * 
     * @param string $boardID ID доски, к которой будет прикреплена карточка
     * @param string $pageDomain Домен сайта, который будет отображаться для этой карточки
     * @param string $pageTitle Title страницы, на которую ведет ссылка. Нигде не отображается.
     * @param string $description Описание карточки. Видно при просмотре карточки
     * @param string $pageURL Ссылка на страницу-источник этой карточки
     * @param \YandexCollectionsAPI\Data\Content $content Перечисление картинок и видео, которые будут прикреплены к карточке {@see \YandexCollectionsAPI\Data\Content}. Можно прикреплять несколько объектов, но выводиться будет только первый. При проверке по id карточки тоже показывает один объект. Непонятно зачем сделали возможность прикреплять несколько.
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Cards/v1_cards_post-docpage/}
     * 
     * Списки аргументов переменной длины - {@link https://www.php.net/manual/ru/functions.arguments.php#functions.variable-arg-list}
     */
    public function insert(string $boardID, string $pageDomain, string $pageTitle, string $description, string $pageURL, Data\Content ...$content): \stdClass {
        $params = [
            'board_id' => $boardID,
            'description' => $description,
            'source_meta' => [
                'page_domain' => $pageDomain,
                'page_title' => $pageTitle,
                'page_url' => $pageURL,
            ]
        ];
        foreach ($content as $value) {
            $tmp = [];
            $tmp['source_type'] = $value->sourceType;
            if ($value->sourceUrl !== NULL) {
                $tmp['source']['url'] = $value->sourceUrl;
            }
            $params['content'][] = $tmp;
        }
        return $this->query('POST', '/v1/cards/', ['json' => $params]);
    }

    /**
     * Изменить существующую карточку
     * 
     * @param string $cardID ID изменяемой карточки
     * @param string|null $boardID ID доски, к которой нужно прикрепить эту карточку. null - не менять
     * @param string|null $description Описание карточки. null - не менять
     * @return \stdClass Ответ от api {@link https://yandex.ru/dev/collections/doc/ref/Cards/v1_cards_id_patch-docpage/}
     */
    public function update(string $cardID, ?string $boardID = NULL, ?string $description = NULL): \stdClass {
        $params = [];
        if ($boardID !== NULL) {
            $params['board_id'] = $boardID;
        }
        if ($description !== NULL) {
            $params['description'] = $description;
        }
        return $this->query('PATCH', '/v1/cards/' . $cardID . '/', ['json' => $params]);
    }

    public function delete(string $id): bool {
        return $this->query('DELETE', '/v1/cards/' . $id);
    }

}

abstract class Base {

    const API_HOST = 'https://api.collections.yandex.net';

    protected $httpClient;
    protected $token;

    public function __construct(\GuzzleHttp\Client $httpClient, string $token) {
        $this->httpClient = $httpClient;
        $this->token = $token;
    }

    /**
     * Запрос к api
     * 
     * @param string $httpMethod GET, POST, PATCH, DELETE
     * @param string $apiMethod Метод api к которому идет обращение
     * @param array $params Параметры api метода
     * @return \stdClass Ответ от api
     */
    protected function query(string $httpMethod, string $apiMethod, array $params = []) {
        $params['headers'] = ['Authorization' => 'OAuth ' . $this->token];
        if ($httpMethod == 'GET') {
            $params['headers']['Accept'] = 'application/json';
        } else if ($httpMethod == 'POST' || $httpMethod == 'PATCH') {
            $params['headers']['Content-Type'] = 'application/json; charset=utf-8';
        }
        $res = $this->httpClient->request($httpMethod, self::API_HOST . $apiMethod, $params);
        if ($res->getStatusCode() == 204) {
            return true;
        }
        return \GuzzleHttp\json_decode($res->getBody()->getContents());
    }

}
