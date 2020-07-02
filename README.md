# IblockWrapper
Обертка над Информационными блоками Bitrix

Упрощаёт доступ к инфоблокам, позволяя не использовать идентификаторы, а использовать символьный код инфоблока.
Упрощает получение данных из инфоблока, 

## Как это работает:

1) Создаем класс для вашего Типа ифоблока и указываем его символьный код
```
  class Info extends IBlockType
  {
      protected static $code = "Info";
  }
```

2) Создаем класс для Инфоблока и указываем его символьный код. Укажите в качестве public const список свойств инфоблока,
имя константы свойства ОБЯЗАТЕЛЬНО должно начинать с PROPERTY_
```
    class News extends Iblock
    {
        public const PROPERTY_TITLE_FOR_MAIN = "TITLE_FOR_MAIN";
        public const PROPERTY_OTHER_DESC_FOR_MAIN = "OTHER_DESC_FOR_MAIN";
        
        public static function getIblockType() {
            return Info::getCode();
        }
    }
```

Переопределение символьного кода инфоблока:
```
    class News extends Iblock
    {
        protected const CODE = "My_News";
        ...
    }
```

Примеры работы:


Теперь без труда можно получить доступ к идентификатору инфоблока: News::getId().
```
    'IBLOCK_ID' => News::getId()
```
Получение списка элементов инфоблока, сигнатура данного метода аналогична CIBlockElement::GetList, однако метод
автоматически подставляет индентификатор инфоблока в Фильтр
```
    News::getElementsList()
```


Идентификатор кэшируется, поэтому доступ в базу для получения ID инфоблока произойдет лишь однажды.

Так же класс предоставляет различные полезные методы для получения данных из нфоблока getElementsList, getSectionList и другие стандартные методы Bitrix.
