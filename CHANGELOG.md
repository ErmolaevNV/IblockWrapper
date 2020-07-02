# Change Log


All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [0.0.2] - 02.07.20
### Добавлено
- Символьный код инфоблока берется из названия класса, если указать protected const CODE в теле класса,
то символьный идентификатор будет извлекаться из неё.

## [0.0.3] - 02.07.20
### Добавлено
- Метод Iblock::getPropertyCodeList для получения списка всех свойств инфоблока указанных в теле класса
- Метод Iblock::getById для получения элемента инфоблока по ID. Метод возвращает все поля класса + свойства,
если они указаны в классе инфоблока;

### Изменено
- Обновлено описание