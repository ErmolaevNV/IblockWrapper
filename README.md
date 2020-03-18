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

2) Создаем класс для Инфоблока и указываем его символьный код. Для удобства можно перечислить в константах все свойства Инфоблока
```
  class News extends Iblock
  {
      protected const CODE = "News";

      public const TITLE_FOR_MAIN = "TITLE_FOR_MAIN";
      public const OTHER_DESC_FOR_MAIN = "OTHER_DESC_FOR_MAIN";

      public static function getIblockType() {
          return Info::getCode();
      }
  }
```

Теперь без труда можно получить доступ к идентификату инфоблока: News::getId().
Идентификатор кэшируется поэтому доступ в базу для получения ID инфоблока произойдет лишь однажды.

Так же класс предоставляет различные полезные методы для получения данных из нфоблока getElementsList, getSectionList и другие стандартные методы Bitrix.
