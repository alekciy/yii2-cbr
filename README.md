Установка
======
1. Создать базу данных:
    ```sql
    CREATE USER IF NOT EXISTS 'yii2basic'@'127.0.0.1' IDENTIFIED BY 'eeb2eiWi'
    CREATE USER IF NOT EXISTS 'yii2basic'@'localhost' IDENTIFIED BY 'eeb2eiWi'
    CREATE DATABASE IF NOT EXISTS yii2basic DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci
    GRANT ALL PRIVILEGES ON yii2basic.* TO 'yii2basic'@'127.0.0.1'
    GRANT ALL PRIVILEGES ON yii2basic.* TO 'yii2basic'@'localhost'
    ```
   либо внести реквизиты доступа к существующей базе в файл `config/db.php`
   
1. Выполнить миграции: `./yii migrate/up`

1. Загрузить курсы за прошлую календарную неделю:
    ```bash
   ./yii cbr/week-load
    ```

1. Прописать в минутный крон команды:
    ```bash
   ./yii cbr
   ./yii cbr/check-currency
    ```
   
API
======
Возможно получение нужного курса по коду и дате. Пример: `/currency?filter[char_code]=USD&filter[date]=2020-02-15`



