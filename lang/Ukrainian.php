<?php
define('PROC_ERROR_ACCT_CREATE', 'Виникла помилка під час створення акаунта');
define('PROC_WRONG_ACCT_PWD', 'Невірний пароль');
define('PROC_CANT_LOG_NONDEF', 'Спроба залогінитись в недефолтний акаунт');
define('PROC_CANT_INS_NEW_FILTER', 'Помилка при створенні фільтру');
define('PROC_FOLDER_EXIST', 'Папка з вказаним ім\'ям неіснує');
define('PROC_CANT_CREATE_FLD', 'Помилка при створенні папки');
define('PROC_CANT_INS_NEW_GROUP', 'Помилка при додаванні нової групи');
define('PROC_CANT_INS_NEW_CONT', 'Помилка при створенні контакту');
define('PROC_CANT_INS_NEW_CONTS', 'Помилка при додаванні нового(их) контакту(ів)');
define('PROC_CANT_ADD_NEW_CONT_TO_GRP', 'Помилка при додаванні контакту(ів) в групу');
define('PROC_ERROR_ACCT_UPDATE', 'Відбулась помилка під час оновлення акаунту');
define('PROC_CANT_UPDATE_CONT_SETTINGS', 'Помилка під час оновлення налаштувань контактів');
define('PROC_CANT_GET_SETTINGS', 'Помилка при одержанні налаштувань');
define('PROC_CANT_UPDATE_ACCT', 'Помилка під час оновлення акаунту');
define('PROC_ERROR_DEL_FLD', 'Відбулась помилка під час видалення папки(ок)');
define('PROC_CANT_UPDATE_CONT', 'Помилка під час оновлення контакту');
define('PROC_CANT_GET_FLDS', 'Помилка при одержанні дерева папок');
define('PROC_CANT_GET_MSG_LIST', 'Помилка при одержанні списка папок');
define('PROC_MSG_HAS_DELETED', 'Лист був видалений з сервера');
define('PROC_CANT_LOAD_CONT_SETTINGS', 'Помилка завантаження налаштувань контактів');
define('PROC_CANT_LOAD_SIGNATURE', 'Помилка завантаження підпису акаунта');
define('PROC_CANT_GET_CONT_FROM_DB', 'Помилка при одержанні контакту з бази даних');
define('PROC_CANT_GET_CONTS_FROM_DB', 'Помилка при одержанні контакту(ів) з бази даних');
define('PROC_CANT_DEL_ACCT_BY_ID', 'Помилка видалення акаунта по ідентифікатору');
define('PROC_CANT_DEL_FILTER_BY_ID', '	Помилка під час видалення фільтру з ідентифікатором');
define('PROC_CANT_DEL_CONT_GROUPS', 'Помилка при видаленні контакту(ів) та/або груп(и)');
define('PROC_WRONG_ACCT_ACCESS', 'Виявлена спроба несанкціонованого доступу до акаунта іншим користувачем');
define('PROC_SESSION_ERROR', 'Попередня сесія була завершена по тайм-ауту');

define('MailBoxIsFull', 'Поштова скринька переповнена');
define('WebMailException', 'Бідбулась помилка');
define('InvalidUid', 'Неправильний UID повідомлення');
define('CantCreateContactGroup', 'Помилка при створенні групи контактів');
define('CantCreateUser', 'Помилка при створенні користувача');
define('CantCreateAccount', 'Помилка при створенні облікового запису');
define('SessionIsEmpty', 'Пуста сесія');
define('FileIsTooBig', 'Файл занадто великий');

define('PROC_CANT_MARK_ALL_MSG_READ', 'Неможливо позначити всі повідомлення прочитаними');
define('PROC_CANT_MARK_ALL_MSG_UNREAD', 'Неможливо позначити всі повідомлення непрочитаними');
define('PROC_CANT_PURGE_MSGS', 'Неможливо видалити позначені на вилучення листи');
define('PROC_CANT_DEL_MSGS', 'Помилка при видаленні листа(ів)');
define('PROC_CANT_UNDEL_MSGS', 'Неможливо помітити як такі , що невидаляються');
define('PROC_CANT_MARK_MSGS_READ', 'Неможливо помітити лист(и), як прочитані');
define('PROC_CANT_MARK_MSGS_UNREAD', 'Неможливо помітити лист(и), як непрочитані');
define('PROC_CANT_SET_MSG_FLAGS', 'Неможливо виставити флаг листу(ам)');
define('PROC_CANT_REMOVE_MSG_FLAGS', 'Неможливо зняти флаг листу(ам)');
define('PROC_CANT_CHANGE_MSG_FLD', 'Помилка під час переміщення листа(ів) в іншу папку');
define('PROC_CANT_SEND_MSG', 'Помилка при відправленні листа');
define('PROC_CANT_SAVE_MSG', 'Помилка при збереженні листа');
define('PROC_CANT_GET_ACCT_LIST', 'Помилка під час отримання списку акаунтів');
define('PROC_CANT_GET_FILTER_LIST', 'Помилка під час отримання списку фільтрів');

define('PROC_CANT_LEAVE_BLANK', 'Поля, помічені *, обов\'язкові до заповнення');

define('PROC_CANT_UPD_FLD', 'Помилка при оновленні папки');
define('PROC_CANT_UPD_FILTER', 'Помилка при оновленні фільтру');

define('ACCT_CANT_ADD_DEF_ACCT', 'Цей акаунт неможливо додати, тому що він використовується як дефолтний іншим користувачем');
define('ACCT_CANT_UPD_TO_DEF_ACCT', 'Неможливо змінити статус даного акаунту на дефолтний');
define('ACCT_CANT_CREATE_IMAP_ACCT', 'Помилка при створенні акаунта (підключення до IMAP4-серверу)');
define('ACCT_CANT_DEL_LAST_DEF_ACCT', 'Неможливо видалити останній дефолтний акаунт');

define('LANG_LoginInfo', 'Вхід в поштову скриньку');
define('LANG_Email', 'E-mail');
define('LANG_Login', 'Логін');
define('LANG_Password', 'Пароль');
define('LANG_IncServer', 'Вхідна пошта');
define('LANG_PopProtocol', 'POP3');
define('LANG_ImapProtocol', 'IMAP4');
define('LANG_IncPort', 'Порт');
define('LANG_OutServer', 'SMTP-сервер');
define('LANG_OutPort', 'Порт');
define('LANG_UseSmtpAuth', 'Вик. SMTP-аутентифікацію');
define('LANG_SignMe', 'Запам\'ятати');
define('LANG_Enter', 'Готово');

define('JS_LANG_TitleLogin', 'Логін');
define('JS_LANG_TitleMessagesListView', 'Список листів');
define('JS_LANG_TitleMessagesList', 'Список листів');
define('JS_LANG_TitleViewMessage', 'Перегляд листа');
define('JS_LANG_TitleNewMessage', 'Новий лист');
define('JS_LANG_TitleSettings', 'Налаштування');
define('JS_LANG_TitleContacts', 'Контакти');

define('JS_LANG_StandardLogin', 'Стандартний&nbsp;логін');
define('JS_LANG_AdvancedLogin', 'Розширений&nbsp;логін');

define('JS_LANG_InfoWebMailLoading', 'Почекайте, йде завантаження&hellip;');
define('JS_LANG_Loading', 'Завантаження&hellip;');
define('JS_LANG_InfoMessagesLoad', 'Почекайте, поки завантажиться увесь список листів&hellip;');
define('JS_LANG_InfoEmptyFolder', 'В папці немає листів');
define('JS_LANG_InfoPageLoading', 'Сторінка завантажується&hellip;');
define('JS_LANG_InfoSendMessage', 'Лист відправлено.');
define('JS_LANG_InfoSaveMessage', 'Лист збережено');
// You have imported 3 new contact(s) into your contacts list.
define('JS_LANG_InfoHaveImported', 'Було імпортовано');
define('JS_LANG_InfoNewContacts', 'нових контактів до списку контактів');
define('JS_LANG_InfoToDelete', 'Щоб видалитиь папку');
define('JS_LANG_InfoDeleteContent', ', 	необхідно спочатку видалити весь її вміст');
define('JS_LANG_InfoDeleteNotEmptyFolders', 'Видалення непустих папок недоступне. Попередньо видаліть весь їх вміст');
define('JS_LANG_InfoRequiredFields', '* обов\'зкові поля');

define('JS_LANG_ConfirmAreYouSure', 'Ви впевнені?');
define('JS_LANG_ConfirmDirectModeAreYouSure', 'Видалені листи будуть НАЗАВЖДИ видалені! Ви впевнені?');
define('JS_LANG_ConfirmSaveSettings', 'Налаштування не були збережені. Виберіть OK для збереження');
define('JS_LANG_ConfirmSaveContactsSettings', 'Налаштування контактів не були збережені. Виберіть OK для збереження');
define('JS_LANG_ConfirmSaveAcctProp', 'Властивості акаунта не були збережені. Виберіть OK для збереження');
define('JS_LANG_ConfirmSaveFilter', 'Властивості фільтру не були збережені. Виберіть OK для збереження');
define('JS_LANG_ConfirmSaveSignature', 'Підпис не був збережений. Виберіть OK для збереження');
define('JS_LANG_ConfirmSavefolders', 'Папки не були збережені. Виберіть OK для збереження');
define('JS_LANG_ConfirmHtmlToPlain', 'Попередження: При змінюванні форматування тексту листа з HTML на простий текст поточне форматування буде скасовано. Виберіть OK для продовження');
define('JS_LANG_ConfirmAddFolder', 'Перед додаванням папки необходідно, зберегти зміни. Виберіть OK для збереження');
define('JS_LANG_ConfirmEmptySubject', 'Ви не вказали тему листа. Хочете продовжити?');

define('JS_LANG_WarningEmailBlank', 'Необхідно заповнити поле E-mail');
define('JS_LANG_WarningLoginBlank', 'Необхідно заповнити поле Логін');
define('JS_LANG_WarningToBlank', 'Необхідно заповнити поле Кому');
define('JS_LANG_WarningServerPortBlank', 'Необхідно заповнити поля POP3 та<br />SMTP сервера/порта');
define('JS_LANG_WarningEmptySearchLine', 'Строка поиска пустая. Введите, пожалуйста подстроку, которую необходимо найти');
define('JS_LANG_WarningMarkListItem', 'Помітьте, хоча б один лист в списку');
define('JS_LANG_WarningFolderMove', 'Неможливо перемістити папку, тому що вона іншого рівня');
define('JS_LANG_WarningContactNotComplete', 'Введіть E-mail або ім\'я');
define('JS_LANG_WarningGroupNotComplete', 'Введіть ім\'я групи');

define('JS_LANG_WarningEmailFieldBlank', 'Необхідно заповнити поле E-mail');
define('JS_LANG_WarningIncServerBlank', 'Необхідно заповнити поле POP3(IMAP4) сервера');
define('JS_LANG_WarningIncPortBlank', 'Необхідно заповнити поле порту POP3(IMAP4) сервера');
define('JS_LANG_WarningIncLoginBlank', 'Необхідно заповнити поле POP3(IMAP4) логіну');
define('JS_LANG_WarningIncPortNumber', 'Необхідно вказати правильне число в полі порта POP3(IMAP4) сервера');
define('JS_LANG_DefaultIncPortNumber', 'Значення порта POP3(IMAP4) по замовчуванню - 110(143)');
define('JS_LANG_WarningIncPassBlank', 'Необхідно заповнити поле POP3(IMAP4) паролю');
define('JS_LANG_WarningOutPortBlank', 'Необхідно заповнити поле порта SMTP сервера');
define('JS_LANG_WarningOutPortNumber', 'Необхідно вказати правильне число в поле порта SMTP сервера');
define('JS_LANG_WarningCorrectEmail', 'Необхідно вказати коректний E-mail');
define('JS_LANG_DefaultOutPortNumber', 'Значення порта SMTP по замовчуванню - 25');

define('JS_LANG_WarningCsvExtention', 'Розширення файлу повинно бути - .csv');
define('JS_LANG_WarningImportFileType', 'Виберіть, будь-ласка, програму, з якої ви хочете імпортувати контакти');
define('JS_LANG_WarningEmptyImportFile', 'Виберіть, будь-ласка, файл');

define('JS_LANG_WarningContactsPerPage', 'Значення поля контактів на сторінку повинно бути позитивним числом');
define('JS_LANG_WarningMessagesPerPage', 'Значення поля листів на сторінку повинно бути позитивним числом');
define('JS_LANG_WarningMailsOnServerDays', 'Необхідно вказати позитивне число в поле кількості днів для зберігання листів на сервері');
define('JS_LANG_WarningEmptyFilter', 'Введіть підстроку, будь-ласка');
define('JS_LANG_WarningEmptyFolderName', 'Введіть, будь-ласка, і\'мя папки');

define('JS_LANG_ErrorConnectionFailed', 'Невдале з\'єднання');
define('JS_LANG_ErrorRequestFailed', 'Завантаження даних небуло завершено');
define('JS_LANG_ErrorAbsentXMLHttpRequest', 'Об\'єкт XMLHttpRequest відсутній');
define('JS_LANG_ErrorWithoutDesc', 'Відбулась невідома помилка');
define('JS_LANG_ErrorParsing', 'Помилка XML');
define('JS_LANG_ResponseText', 'Текст відповіді:');
define('JS_LANG_ErrorEmptyXmlPacket', 'Пустий XML пакет');
define('JS_LANG_ErrorImportContacts', 'Відбулась помилка під час імпорту контактів');
define('JS_LANG_ErrorNoContacts', 'Немає контактів для імпорту');
define('JS_LANG_ErrorCheckMail', 'Поштовий сервіс тимчасово непрацює');

define('JS_LANG_LoggingToServer', 'З\'єднання з поштовим сервером');
define('JS_LANG_GettingMsgsNum', 'Отримання кількості листів');
define('JS_LANG_RetrievingMessage', 'Одержання листів');
define('JS_LANG_DeletingMessage', 'Видалення листа');
define('JS_LANG_DeletingMessages', 'Видалення листів');
define('JS_LANG_Of', 'з');
define('JS_LANG_Connection', 'З\'єднання');
define('JS_LANG_Charset', 'Кодування');
define('JS_LANG_AutoSelect', 'Автоматичний вибір');

define('JS_LANG_Contacts', 'Контакти');
define('JS_LANG_ClassicVersion', 'Класична версія');
define('JS_LANG_Logout', 'Вихід');
define('JS_LANG_Settings', 'Налаштування');

define('JS_LANG_LookFor', 'Слово для пошуку');
define('JS_LANG_SearchIn', 'Шукати в');
define('JS_LANG_QuickSearch', 'Шукати тільки в полях Від, Кому і Тема (швидкий пошук)');
define('JS_LANG_SlowSearch', 'Шукати у всіх листах');
define('JS_LANG_AllMailFolders', 'Всі папки');
define('JS_LANG_AllGroups', 'Всі групи');

define('JS_LANG_NewMessage', 'Новий лист');
define('JS_LANG_CheckMail', 'Перевірити пошту');
define('JS_LANG_ReloadFolders', 'Перезавантажити дерево папок');
define('JS_LANG_EmptyTrash', 'Очистити кошик');
define('JS_LANG_MarkAsRead', 'Помітити прочитаним');
define('JS_LANG_MarkAsUnread', 'Помітити,як не прочитане');
define('JS_LANG_MarkFlag', 'Виставити флаг');
define('JS_LANG_MarkUnflag', 'Зняти флаг');
define('JS_LANG_MarkAllRead', 'Помітити всі листи,як прочитані');
define('JS_LANG_MarkAllUnread', 'Помітити всі листи,як непрочитані');
define('JS_LANG_Reply', 'Відповісти');
define('JS_LANG_ReplyAll', 'Відповісти всім');
define('JS_LANG_Delete', 'Видалити');
define('JS_LANG_Undelete', 'Відмінити видалення');
define('JS_LANG_PurgeDeleted', 'Видалити помічені');
define('JS_LANG_MoveToFolder', 'Перемістити в папку');
define('JS_LANG_Forward', 'Переслати');

define('JS_LANG_HideFolders', 'Приховати папки');
define('JS_LANG_ShowFolders', 'Показати папки');
define('JS_LANG_ManageFolders', 'Налаштування папок');
define('JS_LANG_SyncFolder', 'Синхронізована папка');
define('JS_LANG_NewMessages', 'Нові листи');
define('JS_LANG_Messages', 'Листів');

define('JS_LANG_From', 'Від');
define('JS_LANG_To', 'Кому');
define('JS_LANG_Date', 'Дата');
define('JS_LANG_Size', 'Розмір');
define('JS_LANG_Subject', 'Тема');

define('JS_LANG_FirstPage', 'Перша сторінка');
define('JS_LANG_PreviousPage', 'Попередня сторінка');
define('JS_LANG_NextPage', 'Наступна сторінка');
define('JS_LANG_LastPage', 'Остання сторінка');

define('JS_LANG_SwitchToPlain', 'Переключитись на простий текст');
define('JS_LANG_SwitchToHTML', 'Переключитись в HTML');
define('JS_LANG_AddToAddressBook', 'Додати в адресну книгу');
define('JS_LANG_ClickToDownload', 'Клікніть для завантаження');
define('JS_LANG_View', 'Перегляд');
define('JS_LANG_ShowFullHeaders', 'Показати всі заголовки');
define('JS_LANG_HideFullHeaders', 'Приховати всі заголовки');

define('JS_LANG_MessagesInFolder', 'листів в поштовій скриньці');
define('JS_LANG_YouUsing', 'Ви використали');
define('JS_LANG_OfYour', 'з');
define('JS_LANG_Mb', 'MB');
define('JS_LANG_Kb', 'KB');
define('JS_LANG_B', 'B');

define('JS_LANG_SendMessage', 'Відправити');
define('JS_LANG_SaveMessage', 'Зберегти');
define('JS_LANG_Print', 'Друк');
define('JS_LANG_PreviousMsg', 'Попередній лист');
define('JS_LANG_NextMsg', 'Наступний лист');
define('JS_LANG_AddressBook', 'Адресна книга');
define('JS_LANG_ShowBCC', 'Показати приховані копії');
define('JS_LANG_HideBCC', 'Приховати приховані копії');
define('JS_LANG_CC', 'Копії');
define('JS_LANG_BCC', 'Приховані копії');
define('JS_LANG_ReplyTo', 'Зворотня адреса');
define('JS_LANG_AttachFile', 'Прикріпити файл');
define('JS_LANG_Attach', 'Завантажити');
define('JS_LANG_Re', 'Re');
define('JS_LANG_OriginalMessage', 'Лист,що пересилається');
define('JS_LANG_Sent', 'Відправлено');
define('JS_LANG_Fwd', 'Fwd');
define('JS_LANG_Low', 'Низький');
define('JS_LANG_Normal', 'Звичайний');
define('JS_LANG_High', 'Високий');
define('JS_LANG_Importance', 'Пріоритет');
define('JS_LANG_Close', 'Закрити');

define('JS_LANG_Common', 'Загальне');
define('JS_LANG_EmailAccounts', 'Персональне');

define('JS_LANG_MsgsPerPage', 'Повідомлень на одній сторінці');
define('JS_LANG_DisableRTE', 'Заборонити розширений редактор');
define('JS_LANG_Skin', 'Вигляд');
define('JS_LANG_DefCharset', 'Кодування');
define('JS_LANG_DefCharsetInc', 'Вхідне кодування по замовчуванню');
define('JS_LANG_DefCharsetOut', 'Вихідна кодування по замовчуванню');
define('JS_LANG_DefTimeOffset', 'Часовой пояс');
define('JS_LANG_DefLanguage', 'Мова');
define('JS_LANG_DefDateFormat', 'Формат дати');
define('JS_LANG_ShowViewPane', 'Список листів з переглядом вибраного листа');
define('JS_LANG_Save', 'Зберегти');
define('JS_LANG_Cancel', 'Відміна');
define('JS_LANG_OK', 'Створити');

define('JS_LANG_Remove', 'Видалити');
define('JS_LANG_AddNewAccount', 'Додати новий аккаунт');
define('JS_LANG_Signature', 'Підпис');
define('JS_LANG_Filters', 'Фільтр');
define('JS_LANG_Properties', 'Властивості');
define('JS_LANG_UseForLogin', 'Використовувати налаштування цього аккаунта (логін та пароль) для входу');
define('JS_LANG_MailFriendlyName', 'Ваше ім\'я');
define('JS_LANG_MailEmail', 'E-mail');
define('JS_LANG_MailIncHost', 'Сервер вхідної пошти');
define('JS_LANG_Imap4', 'IMAP4');
define('JS_LANG_Pop3', 'POP3');
define('JS_LANG_MailIncPort', 'Порт');
define('JS_LANG_MailIncLogin', 'Логін');
define('JS_LANG_MailIncPass', 'Пароль');
define('JS_LANG_MailOutHost', 'SMTP сервер');
define('JS_LANG_MailOutPort', 'Порт');
define('JS_LANG_MailOutLogin', 'SMTP логін');
define('JS_LANG_MailOutPass', 'SMTP пароль');
define('JS_LANG_MailOutAuth1', 'Вик. SMTP аутентифікацію');
define('JS_LANG_MailOutAuth2', '(Ви можете залишити поля SMTP логіну та/або паролю порожніми, якщо вони збігаються з полями POP3/(IMAP4) логіну та/або паролю)');
define('JS_LANG_UseFriendlyNm1', 'Використовувати дружнє ім\'я в полі Від');
define('JS_LANG_UseFriendlyNm2', '(Ваше ім\'я &lt;sender@lviv.selfip.com&gt;)');
define('JS_LANG_GetmailAtLogin', 'Одержувати/синхронізувати пошту при вході');
define('JS_LANG_MailMode0', 'Видаляти прийняті листи з поштового серверу');
define('JS_LANG_MailMode1', 'Залишати листи на сервері');
define('JS_LANG_MailMode2', 'Зберігати листи на сервері');
define('JS_LANG_MailsOnServerDays', 'день(дній)');
define('JS_LANG_MailMode3', 'Видаляти повідомлення на поштовому сервері при видаленні їх з Кошику');
define('JS_LANG_InboxSyncType', 'Тип синхронізоції папки Вхідні');

define('JS_LANG_SyncTypeNo', 'Не синхронізувати');
define('JS_LANG_SyncTypeNewHeaders', 'Нові заголовки');
define('JS_LANG_SyncTypeAllHeaders', 'Всі заголовки');
define('JS_LANG_SyncTypeNewMessages', 'Нові листи');
define('JS_LANG_SyncTypeAllMessages', 'Всі листи');
define('JS_LANG_SyncTypeDirectMode', 'Режим прямого доступу');

define('JS_LANG_Pop3SyncTypeEntireHeaders', 'Заголовки');
define('JS_LANG_Pop3SyncTypeEntireMessages', 'Листи');
define('JS_LANG_Pop3SyncTypeDirectMode', 'Режим прямого доступу');

define('JS_LANG_DeleteFromDb', 'Видаляти листи з бази даних, якщо вони не існують на поштовому сервері');

define('JS_LANG_EditFilter', 'Редагувати');
define('JS_LANG_NewFilter', 'Створити фільтр');
define('JS_LANG_Field', 'Поле');
define('JS_LANG_Condition', 'Умова');
define('JS_LANG_ContainSubstring', 'Містить  підстроку');
define('JS_LANG_ContainExactPhrase', 'Містить точну фразу');
define('JS_LANG_NotContainSubstring', 'Не містить підстроку');
define('JS_LANG_FilterDesc_At', 'в поле');
define('JS_LANG_FilterDesc_Field', '');
define('JS_LANG_Action', 'Дія');
define('JS_LANG_DoNothing', 'Нічого не робити');
define('JS_LANG_DeleteFromServer', 'Негайно видалити з сервера');
define('JS_LANG_MarkGrey', 'Помітити,як сіре');
define('JS_LANG_Add', 'Додати');
define('JS_LANG_OtherFilterSettings', 'Інші налаштування фільтру');
define('JS_LANG_ConsiderXSpam', 'Враховувати X-Spam заголовки');
define('JS_LANG_Apply', 'Зберегти');

define('JS_LANG_InsertLink', 'Вставити посилання');
define('JS_LANG_RemoveLink', 'Видалити посилання');
define('JS_LANG_Numbering', 'Нумерація');
define('JS_LANG_Bullets', 'Список');
define('JS_LANG_HorizontalLine', 'Горизонтальна лінія');
define('JS_LANG_Bold', 'Напівжирний');
define('JS_LANG_Italic', 'Курсив');
define('JS_LANG_Underline', 'Підкреслений');
define('JS_LANG_AlignLeft', 'По лівому краю');
define('JS_LANG_Center', 'По центру');
define('JS_LANG_AlignRight', 'По правому краю');
define('JS_LANG_Justify', 'По ширині');
define('JS_LANG_FontColor', 'Колір тексту');
define('JS_LANG_Background', 'Колір фону');
define('JS_LANG_SwitchToPlainMode', 'Переключити в тектовий режим');
define('JS_LANG_SwitchToHTMLMode', 'Переключити в HTML режим');
define('JS_LANG_AddSignatures', 'Добавляти підпис до всіх вихідних листів');
define('JS_LANG_DontAddToReplies', 'Не добавляти підпис до Відповідей');

define('JS_LANG_Folder', 'Папка');
define('JS_LANG_Msgs', 'Листів');
define('JS_LANG_Synchronize', 'Синхронізація');
define('JS_LANG_ShowThisFolder', 'Показувати папку');
define('JS_LANG_Total', 'Всього');
define('JS_LANG_DeleteSelected', 'Видалити вибрані папки');
define('JS_LANG_AddNewFolder', 'Додати нову папку');
define('JS_LANG_NewFolder', 'Нова папка');
define('JS_LANG_ParentFolder', 'Батьківська папка');
define('JS_LANG_NoParent', 'Немає');
define('JS_LANG_OnMailServer', 'Створити папку в поштовому сервісі і на поштовому сервері');
define('JS_LANG_InWebMail', 'Створити папку тільки в поштовому сервісі');
define('JS_LANG_FolderName', 'Ім\'я папки');

define('JS_LANG_ContactsPerPage', 'Кількість контактів на одній сторінці');
define('JS_LANG_WhiteList', '	Використовувати адресну книгу як білий список');

define('JS_LANG_CharsetDefault', 'По замовчуванню');
define('JS_LANG_CharsetArabicAlphabetISO', 'Arabic Alphabet (ISO)');
define('JS_LANG_CharsetArabicAlphabet', 'Arabic Alphabet (Windows)');
define('JS_LANG_CharsetBalticAlphabetISO', 'Baltic Alphabet (ISO)');
define('JS_LANG_CharsetBalticAlphabet', 'Baltic Alphabet (Windows)');
define('JS_LANG_CharsetCentralEuropeanAlphabetISO', 'Central European Alphabet (ISO)');
define('JS_LANG_CharsetCentralEuropeanAlphabet', 'Central European Alphabet (Windows)');
define('JS_LANG_CharsetChineseSimplifiedEUC', 'Chinese Simplified (EUC)');
define('JS_LANG_CharsetChineseSimplifiedGB', 'Chinese Simplified (GB2312)');
define('JS_LANG_CharsetChineseTraditional', 'Chinese Traditional (Big5)');
define('JS_LANG_CharsetCyrillicAlphabetISO', 'Cyrillic Alphabet (ISO)');
define('JS_LANG_CharsetCyrillicAlphabetKOI8R', 'Cyrillic Alphabet (KOI8-R)');
define('JS_LANG_CharsetCyrillicAlphabet', 'Cyrillic Alphabet (Windows)');
define('JS_LANG_CharsetGreekAlphabetISO', 'Greek Alphabet (ISO)');
define('JS_LANG_CharsetGreekAlphabet', 'Greek Alphabet (Windows)');
define('JS_LANG_CharsetHebrewAlphabetISO', 'Hebrew Alphabet (ISO)');
define('JS_LANG_CharsetHebrewAlphabet', 'Hebrew Alphabet (Windows)');
define('JS_LANG_CharsetJapanese', 'Japanese');
define('JS_LANG_CharsetJapaneseShiftJIS', 'Japanese (Shift-JIS)');
define('JS_LANG_CharsetKoreanEUC', 'Korean (EUC)');
define('JS_LANG_CharsetKoreanISO', 'Korean (ISO)');
define('JS_LANG_CharsetLatin3AlphabetISO', 'Latin 3 Alphabet (ISO)');
define('JS_LANG_CharsetTurkishAlphabet', 'Turkish Alphabet');
define('JS_LANG_CharsetUniversalAlphabetUTF7', 'Universal Alphabet (UTF-7)');
define('JS_LANG_CharsetUniversalAlphabetUTF8', 'Universal Alphabet (UTF-8)');
define('JS_LANG_CharsetVietnameseAlphabet', 'Vietnamese Alphabet (Windows)');
define('JS_LANG_CharsetWesternAlphabetISO', 'Western Alphabet (ISO)');
define('JS_LANG_CharsetWesternAlphabet', 'Western Alphabet (Windows)');

define('JS_LANG_TimeDefault', 'По замовчуванню');
define('JS_LANG_TimeEniwetok', 'Eniwetok, Kwajalein, Dateline Time');
define('JS_LANG_TimeMidwayIsland', 'Midway Island, Samoa');
define('JS_LANG_TimeHawaii', 'Hawaii');
define('JS_LANG_TimeAlaska', 'Alaska');
define('JS_LANG_TimePacific', 'Pacific Time (US & Canada); Tijuana');
define('JS_LANG_TimeArizona', 'Arizona');
define('JS_LANG_TimeMountain', 'Mountain Time (US & Canada)');
define('JS_LANG_TimeCentralAmerica', 'Central America');
define('JS_LANG_TimeCentral', 'Central Time (US & Canada)');
define('JS_LANG_TimeMexicoCity', 'Mexico City, Tegucigalpa');
define('JS_LANG_TimeSaskatchewan', 'Saskatchewan');
define('JS_LANG_TimeIndiana', 'Indiana (East)');
define('JS_LANG_TimeEastern', 'Eastern Time (US & Canada)');
define('JS_LANG_TimeBogota', 'Bogota, Lima, Quito');
define('JS_LANG_TimeSantiago', 'Santiago');
define('JS_LANG_TimeCaracas', 'Caracas, La Paz');
define('JS_LANG_TimeAtlanticCanada', 'Atlantic Time (Canada)');
define('JS_LANG_TimeNewfoundland', 'Newfoundland');
define('JS_LANG_TimeGreenland', 'Greenland');
define('JS_LANG_TimeBuenosAires', 'Buenos Aires, Georgetown');
define('JS_LANG_TimeBrasilia', 'Brasilia');
define('JS_LANG_TimeMidAtlantic', 'Mid-Atlantic');
define('JS_LANG_TimeCapeVerde', 'Cape Verde Is.');
define('JS_LANG_TimeAzores', 'Azores');
define('JS_LANG_TimeMonrovia', 'Casablanca, Monrovia');
define('JS_LANG_TimeGMT', 'Dublin, Edinburgh, Lisbon, London');
define('JS_LANG_TimeBerlin', 'Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna');
define('JS_LANG_TimePrague', 'Belgrade, Bratislava, Budapest, Ljubljana, Prague');
define('JS_LANG_TimeParis', 'Brussels, Copenhagen, Madrid, Paris');
define('JS_LANG_TimeSarajevo', 'Sarajevo, Skopje, Sofija, Warsaw, Zagreb');
define('JS_LANG_TimeWestCentralAfrica', 'West Central Africa');
define('JS_LANG_TimeAthens', 'Athens, Istanbul, Minsk');
define('JS_LANG_TimeEasternEurope', 'Bucharest');
define('JS_LANG_TimeCairo', 'Cairo');
define('JS_LANG_TimeHarare', 'Harare, Pretoria');
define('JS_LANG_TimeHelsinki', 'Helsinki, Riga, Tallinn, Vilnius');
define('JS_LANG_TimeIsrael', 'Israel, Jerusalem Standard Time');
define('JS_LANG_TimeBaghdad', 'Baghdad');
define('JS_LANG_TimeArab', 'Arab, Kuwait, Riyadh');
define('JS_LANG_TimeMoscow', 'Moscow, St. Petersburg, Volgograd');
define('JS_LANG_TimeEastAfrica', 'East Africa, Nairobi');
define('JS_LANG_TimeTehran', 'Tehran');
define('JS_LANG_TimeAbuDhabi', 'Abu Dhabi, Muscat');
define('JS_LANG_TimeCaucasus', 'Baku, Tbilisi, Yerevan');
define('JS_LANG_TimeKabul', 'Kabul');
define('JS_LANG_TimeEkaterinburg', 'Ekaterinburg');
define('JS_LANG_TimeIslamabad', 'Islamabad, Karachi, Sverdlovsk, Tashkent');
define('JS_LANG_TimeBombay', 'Calcutta, Chennai, Mumbai, New Delhi, India Standard Time');
define('JS_LANG_TimeNepal', 'Kathmandu, Nepal');
define('JS_LANG_TimeAlmaty', 'Almaty, Novosibirsk, North Central Asia');
define('JS_LANG_TimeDhaka', 'Astana, Dhaka');
define('JS_LANG_TimeSriLanka', 'Sri Jayawardenepura, Sri Lanka');
define('JS_LANG_TimeRangoon', 'Rangoon');
define('JS_LANG_TimeBangkok', 'Bangkok, Hanoi, Jakarta');
define('JS_LANG_TimeKrasnoyarsk', 'Krasnoyarsk');
define('JS_LANG_TimeBeijing', 'Beijing, Chongqing, Hong Kong SAR, Urumqi');
define('JS_LANG_TimeIrkutsk', 'Irkutsk, Ulaan Bataar');
define('JS_LANG_TimeSingapore', 'Kuala Lumpur, Singapore');
define('JS_LANG_TimePerth', 'Perth, Western Australia');
define('JS_LANG_TimeTaipei', 'Taipei');
define('JS_LANG_TimeTokyo', 'Osaka, Sapporo, Tokyo');
define('JS_LANG_TimeSeoul', 'Seoul, Korea Standard time');
define('JS_LANG_TimeYakutsk', 'Yakutsk');
define('JS_LANG_TimeAdelaide', 'Adelaide, Central Australia');
define('JS_LANG_TimeDarwin', 'Darwin');
define('JS_LANG_TimeBrisbane', 'Brisbane, East Australia');
define('JS_LANG_TimeSydney', 'Canberra, Melbourne, Sydney, Hobart');
define('JS_LANG_TimeGuam', 'Guam, Port Moresby');
define('JS_LANG_TimeHobart', 'Hobart, Tasmania');
define('JS_LANG_TimeVladivostock', 'Vladivostok');
define('JS_LANG_TimeMagadan', 'Magadan, Solomon Is., New Caledonia');
define('JS_LANG_TimeWellington', 'Auckland, Wellington');
define('JS_LANG_TimeFiji', 'Fiji Islands, Kamchatka, Marshall Is.');
define('JS_LANG_TimeTonga', 'Nuku\'alofa, Tonga,');

define('LanguageEnglish', 'Англійська');
define('LanguageCatala', 'Каталонська');
define('LanguageNederlands', 'Голандська');
define('LanguageFrench', 'Французська');
define('LanguageGerman', 'Німецька');
define('LanguageItaliano', 'Італійська');
define('LanguagePortuguese', 'Португальська');
define('LanguageEspanyol', 'Іспанска');
define('LanguageSwedish', 'Шведська');
define('LanguageTurkish', 'Турецька');

define('JS_LANG_DateDefault', 'По замовчуванню');
define('JS_LANG_DateDDMMYY', 'DD/MM/YY');
define('JS_LANG_DateMMDDYY', 'MM/DD/YY');
define('JS_LANG_DateDDMonth', 'DD Month (01 Бер)');
define('JS_LANG_DateAdvanced', 'Інший');

define('JS_LANG_NewContact', 'Новий контакт');
define('JS_LANG_NewGroup', 'Нова група');
define('JS_LANG_AddContactsTo', 'Додати контакти в');
define('JS_LANG_ImportContacts', 'Імпорт контактів');

define('JS_LANG_Name', 'І\'мя');
define('JS_LANG_Email', 'E-mail');
define('JS_LANG_DefaultEmail', 'E-mail');
define('JS_LANG_NotSpecifiedYet', 'Ще не вказана');
define('JS_LANG_ContactName', 'І\'мя');
define('JS_LANG_Birthday', 'День Народження');
define('JS_LANG_Month', 'Місяць');
define('JS_LANG_January', 'Січень');
define('JS_LANG_February', 'Лютий');
define('JS_LANG_March', 'Березень');
define('JS_LANG_April', 'Квітень');
define('JS_LANG_May', 'Травень');
define('JS_LANG_June', 'Червень');
define('JS_LANG_July', 'Липень');
define('JS_LANG_August', 'Серпень');
define('JS_LANG_September', 'Вересень');
define('JS_LANG_October', 'Жовтень');
define('JS_LANG_November', 'Листопад');
define('JS_LANG_December', 'Грудень');
define('JS_LANG_Day', 'День');
define('JS_LANG_Year', 'Рік');
define('JS_LANG_UseFriendlyName1', 'Використовувати ім\'я');
define('JS_LANG_UseFriendlyName2', '(наприклад, Вася Пупкін &lt;vasya@lviv.selfip.com&gt;)');
define('JS_LANG_Personal', 'Дім');
define('JS_LANG_PersonalEmail', 'Домашній E-mail');
define('JS_LANG_StreetAddress', 'Адреса');
define('JS_LANG_City', 'Місто');
define('JS_LANG_Fax', 'Факс');
define('JS_LANG_StateProvince', 'Регіон');
define('JS_LANG_Phone', 'Телефон');
define('JS_LANG_ZipCode', 'Індекс');
define('JS_LANG_Mobile', 'Мобільний');
define('JS_LANG_CountryRegion', 'Країна');
define('JS_LANG_WebPage', 'Web');
define('JS_LANG_Go', 'Перевірити');
define('JS_LANG_Home', 'Дім');
define('JS_LANG_Business', 'Робота');
define('JS_LANG_BusinessEmail', 'Робочий E-mail');
define('JS_LANG_Company', 'Компанія');
define('JS_LANG_JobTitle', 'Назва работи');
define('JS_LANG_Department', 'Департамент');
define('JS_LANG_Office', 'Офіс');
define('JS_LANG_Pager', 'Пейджер');
define('JS_LANG_Other', 'Інше');
define('JS_LANG_OtherEmail', 'Додатковий E-mail');
define('JS_LANG_Notes', 'Замітки');
define('JS_LANG_Groups', 'Групи');
define('JS_LANG_ShowAddFields', 'Показати додаткові поля');
define('JS_LANG_HideAddFields', 'Приховати додаткові поля');
define('JS_LANG_EditContact', 'Редагувати');
define('JS_LANG_GroupName', 'Ім\'я групи');
define('JS_LANG_AddContacts', 'Додати контакти');
define('JS_LANG_CommentAddContacts', '(Якщо Ви хочете вказати більш ніж одну адрусу, розділяйте їх комами)');
define('JS_LANG_CreateGroup', 'Створити групу');
define('JS_LANG_Rename', 'Переіменувати');
define('JS_LANG_MailGroup', 'Написати групі');
define('JS_LANG_RemoveFromGroup', 'Видалити з групи');
define('JS_LANG_UseImportTo', '	Використовуйте імпорт для копіювання контактів з Microsoft Outlook, Microsoft Outlook Express в ваш список контактів.');
define('JS_LANG_Outlook1', 'Microsoft Outlook 2000/XP/2003');
define('JS_LANG_Outlook2', 'Microsoft Outlook Express 6');
define('JS_LANG_SelectImportFile', 'Виберіть файл (.CSV формату), який будете імпортувати');
define('JS_LANG_Import', 'Імпортувати');
define('JS_LANG_ContactsMessage', 'Сторінка контактів');
define('JS_LANG_ContactsCount', 'контакт(ів)');
define('JS_LANG_GroupsCount', 'груп(а)');

// webmail 4.1 constants
define('PicturesBlocked', 'Малюнки в повідомленні заблоковані з міркувань безпеки');
define('ShowPictures', 'Показати малюнки');
define('ShowPicturesFromSender', 'Завжди показувати зображення в повідомленнях від даного відправника');
define('AlwaysShowPictures', 'Завжди показувати зображення в повідомленнях');

define('TreatAsOrganization', 'Розглядати як організацію');

define('WarningGroupAlreadyExist', 'Група з такою назвою вже існує');
define('WarningCorrectFolderName', 'Необхідно вказати коректне ім\'я папки');
define('WarningLoginFieldBlank', 'Необхідно вказати ім\'я користувача');
define('WarningCorrectLogin', 'Необхідно вказати коректне значення поля Логін');
define('WarningPassBlank', 'Необхідно вказати пароль');
define('WarningCorrectIncServer', 'Необхідно вказати коректне значення поля POP3(IMAP) сервер');
define('WarningCorrectSMTPServer', 'Необхідно вказати коректне значення поля SMTP сервер');
define('WarningFromBlank', 'Необхідно вказати значение поля Кому');
define('WarningAdvancedDateFormat', 'Вкажіть, дудь-ласка, формат дати');

define('AdvancedDateHelpTitle', 'Розширена дата');
define('AdvancedDateHelpIntro', 'Якщо виберете опцію &quot;Інший&quot;, то тут ви можете редагувати особистий формата дати, в якому буде відображатись дата в списку листів. Наступні опції можна використовувати, розділяючи їх знаками \':\' або \'/\':');
define('AdvancedDateHelpConclusion', 'Наприклад , якщо ви вкажете &quot;mm/dd/yyyy&quot; в текстовому полі , то дата буде відображатись як місяць/день/рік (ось так: 11/23/2008)');
define('AdvancedDateHelpDayOfMonth', 'День в місяці (від 1 до 31)');
define('AdvancedDateHelpNumericMonth', 'Місяць (від 1 до 12)');
define('AdvancedDateHelpTextualMonth', 'Місяць (від Січ до Гру)');
define('AdvancedDateHelpYear2', 'Рік, 2 цифри');
define('AdvancedDateHelpYear4', 'Рік, 4 цифри');
define('AdvancedDateHelpDayOfYear', 'День в році (від 1 до 366)');
define('AdvancedDateHelpQuarter', 'Квартал');
define('AdvancedDateHelpDayOfWeek', 'День в тижні (від Пон до Нед)');
define('AdvancedDateHelpWeekOfYear', 'Тиждень в році (від 1 до 53)');

define('InfoNoMessagesFound', '	Жодного листа не знайдено');
define('ErrorSMTPConnect', 'Помилка з\'єднання з SMTP сервером');
define('ErrorSMTPAuth', 'Невірний логін та/або пароль. Невдала аутентифікація');
define('ReportMessageSent', 'Ваш лист відправлено');
define('ReportMessageSaved', 'Ваш лист збережено');
define('ErrorPOP3Connect', 'Помилка з\'єднання з POP3 сервером');
define('ErrorIMAP4Connect', 'Помилка з\'єднання з IMAP4 сервером');
define('ErrorPOP3IMAP4Auth', 'Невірний логін та/або пароль');
define('ErrorGetMailLimit', 'Перевищено ліміт використання вашої скриньки');

define('ReportSettingsUpdatedSuccessfuly', 'Налаштування збережено');
define('ReportAccountCreatedSuccessfuly', 'Успішно створено');
define('ReportAccountUpdatedSuccessfuly', 'Аккаунт успішно оновлено');
define('ConfirmDeleteAccount', 'Ви дійсно хочете видалити акаунт ?');
define('ReportFiltersUpdatedSuccessfuly', 'Фільтр успішно обновлено');
define('ReportSignatureUpdatedSuccessfuly', 'Підпис успішно оновлено');
define('ReportFoldersUpdatedSuccessfuly', 'Папки успішно оновлені');
define('ReportContactsSettingsUpdatedSuccessfuly', 'Налаштування контактів успішно оновлено');

define('ErrorInvalidCSV', 'Вибраний CSV файл має неправильний формат');
// The group "guies" was successfully added.
define('ReportGroupSuccessfulyAdded1', 'Група');
define('ReportGroupSuccessfulyAdded2', 'була успішно добавлена');
define('ReportGroupUpdatedSuccessfuly', 'Група успішно оновлена');
define('ReportContactSuccessfulyAdded', 'Контакт успішно доданий');
define('ReportContactUpdatedSuccessfuly', 'Контакт успішно оновлено');
// Contact(s) was added to group "friends".
define('ReportContactAddedToGroup', 'Контакт доданий в групу');
define('AlertNoContactsGroupsSelected', 'Жодні групи чи контакти не вибрані');

define('InfoListNotContainAddress', 'Якщо в списку немає адреси, яку ви шукаєте, продовжуйте набирати його перші букви');

define('DirectAccess', 'П');
define('DirectAccessTitle', 'Режим прямого доступу. Ви отримуєте доступ до листів напряму з поштового серверу');

define('FolderInbox', 'Вхідні');
define('FolderSentItems', 'Відправлені');
define('FolderDrafts', 'Чорновики');
define('FolderTrash', 'Кошик');

define('LanguageDanish', 'Датська');
define('LanguagePolish', 'Польська');

define('FileLargerAttachment', 'Розмір файлу перевищує ліміт допустимого прикріплюваних файлів');
define('FilePartiallyUploaded', 'Відбулась невідома помилка. Завантажено лише частину файла');
define('NoFileUploaded', 'Жодного файлу небуло завантажено');
define('MissingTempFolder', 'Тимчасова папка відсутня');
define('MissingTempFile', 'Тимчасовий файл відсутній');
define('UnknownUploadError', 'Відбулась невідома помилка');
define('FileLargerThan', 'Помилка завантаження файлу. Можливо, файл більший, ніж ');
define('PROC_CANT_LOAD_DB', 'Увійти до поштової скриньки ,зараз можливо лише за допомогою Light версії ,клікнувши на посилання внизу екрана');
define('PROC_CANT_LOAD_LANG', 'Відсутній мовний файл');
define('PROC_CANT_LOAD_ACCT', 'Поштовий сервіс тимчасово непрацює');

define('DomainDosntExist', 'Такого домена неіснує в поштовому сервері');
define('ServerIsDisable', 'Викокистання поштового сервера заборонено адміністратором');

define('PROC_ACCOUNT_EXISTS', 'Неможливо створити, такий акаунт вже існує');
define('PROC_CANT_GET_MESSAGES_COUNT', 'Помилка при спробі отримати кількість повідомлень');
define('PROC_CANT_MAIL_SIZE', 'Помилка при спробі отримати розмір поштової скриньки');

define('Organization', 'Організація');
define('WarningOutServerBlank', 'Необхідно заповнити поле SMTP сервера');

//
define('JS_LANG_Refresh', 'Оновити');
define('JS_LANG_MessagesInInbox', 'Листів');
define('JS_LANG_InfoEmptyInbox', 'Немає листів');

// webmail 4.2 constants
define('LanguagePortugueseBrazil', 'Португало-Бразильська');
define('LanguageHungarian', 'Угорська');

define('BackToList', 'Назад');
define('InfoNoContactsGroups', 'Список контактів та груп пустий');
define('InfoNewContactsGroups', 'Ви можете створювати нові контакти/групи або імпортувати контакти з .CSV файлу в форматі MS Outlook');
define('DefTimeFormat', 'Формат часу');
define('SpellNoSuggestions', 'немає варіантів');
define('SpellWait', 'почекайте, будь-ласка&hellip;');

define('InfoNoMessageSelected', 'Не вибрано жодного листа');
define('InfoSingleDoubleClick', 'Клікніть один раз на листі , щоб переглянути його зміст внизу вікна, або два рази для того щоб відкрити лист в повному вікні');	

// calendar
define('TitleDay', 'Перегляд дня');
define('TitleWeek', 'Перегляд тижня');
define('TitleMonth', 'Перегляд місяця');

define('ErrorNotSupportBrowser', 'Календар не підтримує ваш браузер. Використовуйте для цього FireFox 2.0 і вище, Opera 9.0 і вище, Internet Explorer 6.0 і вище, Safari 3.0.2 і вище');
define('ErrorTurnedOffActiveX', 'Можливо, у вас відключена підтримка ActiveX. Необхідно включити її для користування Календарем');

define('Calendar', 'Календар');

define('TabDay', 'День');
define('TabWeek', 'Тиждень');
define('TabMonth', 'Місяць');

define('ToolNewEvent', 'Нова&nbsp;подія');
define('ToolBack', 'Назад');
define('ToolToday', 'Сьогодні');
define('AltNewEvent', 'Нова подія');
define('AltBack', 'Назад');
define('AltToday', 'Сьогодні');
define('CalendarHeader', 'Календар');
define('CalendarsManager', 'Менеджер календарів');

define('CalendarActionNew', 'Новий календар');
define('EventHeaderNew', 'Нова подія');
define('CalendarHeaderNew', 'Новий календар');

define('EventSubject', 'Тема');
define('EventCalendar', 'Календар');
define('EventFrom', 'Від');
define('EventTill', 'до');
define('CalendarDescription', 'Опис');
define('CalendarColor', 'Колір');
define('CalendarName', 'Назва календаря');
define('CalendarDefaultName', 'Мій календар');

define('ButtonSave', 'Зберегти');
define('ButtonCancel', 'Відмінити');
define('ButtonDelete', 'Видалити');

define('AltPrevMonth', 'Попередній місяць');
define('AltNextMonth', 'Наступний місяць');

define('CalendarHeaderEdit', 'Редагування календаря');
define('CalendarActionEdit', 'Редагувати календар');
define('ConfirmDeleteCalendar', 'Ви впевнені, що хочете видалити календар');
define('InfoDeleting', 'Видалення&hellip;');
define('WarningCalendarNameBlank', 'Неможна залишати поле назви календаря пустим');
define('ErrorCalendarNotCreated', 'Календар не створено');
define('WarningSubjectBlank', 'Неможна залишати поле теми пустим');
define('WarningIncorrectTime', 'Вказаний час містить недопустимі символи');
define('WarningIncorrectFromTime', 'Від ,-час некоректний');
define('WarningIncorrectTillTime', 'До ,-час некоректний');
define('WarningStartEndDate', 'Кінцева дата повинна бути більшою або такою ж, як початкова');
define('WarningStartEndTime', 'Кінцевийй час повинен бути більшим від початкового');
define('WarningIncorrectDate', 'Некоректно вказана дата');
define('InfoLoading', 'Завантажується&hellip;');
define('EventCreate', 'Створити подію');
define('CalendarHideOther', 'Приховати інші календарі');
define('CalendarShowOther', 'Показати інші календарі');
define('CalendarRemove', 'Видалити календар');
define('EventHeaderEdit', 'Редагування події');

define('InfoSaving', 'Збереження&hellip;');
define('SettingsDisplayName', 'Ім\'я , що відображується');
define('SettingsTimeFormat', 'Формат часу');
define('SettingsDateFormat', 'Формат дати');
define('SettingsShowWeekends', 'Показувати вихідні дні');
define('SettingsWorkdayStarts', 'Робочий день починається з');
define('SettingsWorkdayEnds', 'закінчується в');
define('SettingsShowWorkday', 'Виділяти робочий день');
define('SettingsWeekStartsOn', 'Тиждень починається в');
define('SettingsDefaultTab', 'Таб по замовчуванню');
define('SettingsCountry', 'Країна');
define('SettingsTimeZone', 'Часовий пояс');
define('SettingsAllTimeZones', 'Всі часові пояси');

define('WarningWorkdayStartsEnds', 'Час закінчення робочого дня повинен бути більшим ніж час початку робочого дня');
define('ReportSettingsUpdated', 'Налаштування успішно збережено');

define('SettingsTabCalendar', 'Календар');

define('FullMonthJanuary', 'Січень');
define('FullMonthFebruary', 'Лютий');
define('FullMonthMarch', 'Березень');
define('FullMonthApril', 'Квітень');
define('FullMonthMay', 'Травень');
define('FullMonthJune', 'Червень');
define('FullMonthJuly', 'Липень');
define('FullMonthAugust', 'Серпень');
define('FullMonthSeptember', 'Вересень');
define('FullMonthOctober', 'Жовтень');
define('FullMonthNovember', 'Листопад');
define('FullMonthDecember', 'Грудень');

define('ShortMonthJanuary', 'Січ');
define('ShortMonthFebruary', 'Лют');
define('ShortMonthMarch', 'Бер');
define('ShortMonthApril', 'Кві');
define('ShortMonthMay', 'Тра');
define('ShortMonthJune', 'Чер');
define('ShortMonthJuly', 'Лип');
define('ShortMonthAugust', 'Сер');
define('ShortMonthSeptember', 'Вер');
define('ShortMonthOctober', 'Жов');
define('ShortMonthNovember', 'Лис');
define('ShortMonthDecember', 'Гру');

define('FullDayMonday', 'Понеділок');
define('FullDayTuesday', 'Вівторок');
define('FullDayWednesday', 'Середа');
define('FullDayThursday', 'Четвер');
define('FullDayFriday', 'П\'ятниця');
define('FullDaySaturday', 'Субота');
define('FullDaySunday', 'Неділя');

define('DayToolMonday', 'Пн');
define('DayToolTuesday', 'Вт');
define('DayToolWednesday', 'Ср');
define('DayToolThursday', 'Чт');
define('DayToolFriday', 'Пт');
define('DayToolSaturday', 'Сб');
define('DayToolSunday', 'Нд');

define('CalendarTableDayMonday', 'Пн');
define('CalendarTableDayTuesday', 'Вт');
define('CalendarTableDayWednesday', 'Ср');
define('CalendarTableDayThursday', 'Чт');
define('CalendarTableDayFriday', 'Пт');
define('CalendarTableDaySaturday', 'Сб');
define('CalendarTableDaySunday', 'Нд');

define('ErrorParseJSON', 'Відбулась помилка парсингу JSON відповіді сервера');

define('ErrorLoadCalendar', 'Неможливо завантажити календарі');
define('ErrorLoadEvents', 'Неможливо завантажити події');
define('ErrorUpdateEvent', 'Неможливо зберегти події');
define('ErrorDeleteEvent', 'Неможливо видалити подію');
define('ErrorUpdateCalendar', 'Неможливо зберегти календар');
define('ErrorDeleteCalendar', 'Неможливо видалити календар');
define('ErrorGeneral', 'На сервері відбулась помилка. Спробуйте пізніше');

// webmail 4.3 constants
define('SharedTitleEmail', 'E-mail');
define('ShareHeaderEdit', 'Відкриття доступу');
define('ShareActionEdit', 'ВІдкрити доступ');
define('CalendarPublicate', 'Відкрити загальний доступ до цього календаря');
define('CalendarPublicationLink', 'Посилання');
define('ShareCalendar', 'Загальний доступ для окремих користувачів');
define('SharePermission1', 'Вносити зміни та надавати доступ');
define('SharePermission2', 'Вносити зміни');
define('SharePermission3', 'Переглядати всі відомості про міроприємства');
define('SharePermission4', 'Переглядати інфомацію тільки про вільний та зайнятий час');
define('ButtonClose', 'Закрити');
define('WarningEmailFieldFilling', 'Необхідно заповнити поле E-mail');
define('EventHeaderView', 'Перегляд події');
define('ErrorUpdateSharing', 'Неможливо зберегти дані про загальний доступ');
define('ErrorUpdateSharing1', 'Неможливо надати доступ до календаря користувачу %s, так як він не зареєстрований в системі');
define('ErrorUpdateSharing2', 'Неможливо надати доступ до календаря користувачу %s');
define('ErrorUpdateSharing3', 'Користувачу %s вже надано доступ до цього календаря');
define('Title_MyCalendars', 'Мої календарі');
define('Title_SharedCalendars', 'Інші календарі');
define('ErrorGetPublicationHash', 'Неможливо надати загальний доступ до цього календаря');
define('ErrorGetSharing', 'Неможливо надати доступ до цього календаря');
define('CalendarPublishedTitle', 'Цей календар опубліковано');
define('RefreshSharedCalendars', 'Оновити інші календарі');
define('Title_CheckSharedCalendars', 'Перевірити наявність інших календарів');

define('GroupMembers', 'Учасники');

define('ReportMessagePartDisplayed', 'Зверніть увагу, що лист відображений не повністю');
define('ReportViewEntireMessage', 'Ви можете переглянути його цілком,');
define('ReportClickHere', 'клікнувши тут');
define('ErrorContactExists', 'Контакт з таким іменем та E-mail вже існує');

define('Attachments', 'Вкладення');

define('InfoGroupsOfContact', 'Контакт є учасником тих груп, котрі вже помічені');
define('AlertNoContactsSelected', 'Жодного контакту невибрано');
define('MailSelected', 'Написати вибраним адресатам');
define('CaptionSubscribed', 'Підписка');

define('OperationSpam', 'Спам');
define('OperationNotSpam', 'Не спам');
define('FolderSpam', 'Спам');

// webmail 4.4 contacts
define('ContactMail', 'Написати');
define('ContactViewAllMails', 'Переглянути листи з цим контактом');
define('ContactsMailThem', 'Написати');
define('DateToday', 'Сьогодні');
define('DateYesterday', 'Вчора');
define('MessageShowDetails', 'Показати деталі');
define('MessageHideDetails', 'Приховати деталі');
define('MessageNoSubject', 'Без теми');
// john@gmail.com to nadine@gmail.com
define('MessageForAddr', 'для');
define('SearchClear', 'Відмінити пошук');
// Search results for "search string" in Inbox folder:
define('SearchResultsInFolder', 'Результати пошуку для "#s" в папці #f:');
// Search results for "search string" in all mail folders:
define('SearchResultsInAllFolders', 'Результати пошуку для "#s" в усіх папках:');
define('AutoresponderTitle', 'Автовідповідач');
define('AutoresponderEnable', 'Увімкнути автовідповідач');
define('AutoresponderSubject', 'Тема');
define('AutoresponderMessage', 'Лист');
define('ReportAutoresponderUpdatedSuccessfuly', 'Автовідповідач успішно оновлено');
define('FolderQuarantine', 'Карантин');

// calendar
define('EventRepeats', 'Повтор');
define('NoRepeats', 'Не повторюється');
define('DailyRepeats', 'Кожен день');
define('WorkdayRepeats', 'Кожен тиждень (Пн. - Пт.)');
define('OddDayRepeats', 'Кожен Пн., Ср. та Пт.');
define('EvenDayRepeats', 'Кожен Вт. та Чт.');
define('WeeklyRepeats', 'Кожен тиждень');
define('MonthlyRepeats', 'Кожен місяць');
define('YearlyRepeats', 'Кожен рік');
define('RepeatsEvery', 'Повторювати кожен');
define('ThisInstance', 'Тільки цього разу');
define('AllEvents', 'Всі міроприємства цієї серії');
define('AllFollowing', 'Всі настуні');
define('ConfirmEditRepeatEvent', 'Змінити тільки цю подію, всі події в цій серії або і цю, і всі наступні події в цій серії ?');
define('RepeatEventHeaderEdit', 'Змінити повторювані події');
define('First', 'Перший');
define('Second', 'Другий');
define('Third', 'Третій');
define('Fourth', 'Четвертий');
define('Last', 'Останній');
define('Every', 'Кожен');
define('SetRepeatEventEnd', 'Закінчення');
define('NoEndRepeatEvent', 'Немає дати закінчення');
define('EndRepeatEventAfter', 'Закінчити після');
define('Occurrences', 'повторів');
define('EndRepeatEventBy', 'Закінчити');
define('EventCommonDataTab', 'Основні деталі');
define('EventRepeatDataTab', 'Деталі повторень');
define('RepeatEventNotPartOfASeries', 'Ця подія була змінена і більше не входить в серію');
define('UndoRepeatExclusion', 'Відмінити зміни, щоб включити його в серію');

define('MonthMoreLink', 'ще %d...');
define('NoNewSharedCalendars', 'Немає нових календарів');
define('NNewSharedCalendars', 'Знайдено %d нових календарів');
define('OneNewSharedCalendars', 'Знайдено 1 новий календар');
define('ConfirmUndoOneRepeat', 'Відновити подію в серії повторів?');

define('RepeatEveryDayInfin', 'Кожен день');
define('RepeatEveryDayTimes', 'Кожен день %TIMES% разів');
define('RepeatEveryDayUntil', 'Кожен день по %UNTIL%');
define('RepeatDaysInfin', 'Кожен %PERIOD% день');
define('RepeatDaysTimes', 'Кожен %PERIOD% день %TIMES% разів');
define('RepeatDaysUntil', 'Кожен %PERIOD% день по %UNTIL%');

define('RepeatEveryWeekWeekdaysInfin', 'Кожен тиждень в робочі дні');
define('RepeatEveryWeekWeekdaysTimes', 'Кожен тиждень в робочі дні, %TIMES% разів');
define('RepeatEveryWeekWeekdaysUntil', 'Кожен тиждень в робочі дні по %UNTIL%');
define('RepeatWeeksWeekdaysInfin', 'Кожен %PERIOD% тиждень в робочі дні');
define('RepeatWeeksWeekdaysTimes', 'Кожен %PERIOD% тиждень в робочі дні %TIMES% разів');
define('RepeatWeeksWeekdaysUntil', 'Кожен %PERIOD% тиждень в робочі дні по %UNTIL%');

define('RepeatEveryWeekInfin', 'Кожен тиждень по %DAYS%');
define('RepeatEveryWeekTimes', 'Кожен тиждень по %DAYS% %TIMES% разів');
define('RepeatEveryWeekUntil', 'Кожен тиждень по %DAYS% по %UNTIL%');
define('RepeatWeeksInfin', 'Кожен %PERIOD% тиждень по %DAYS%');
define('RepeatWeeksTimes', 'Кожен %PERIOD% тиждень по %DAYS%, %TIMES% разів');
define('RepeatWeeksUntil', 'Кожен %PERIOD% тиждень по %DAYS% по %UNTIL%');

define('RepeatEveryMonthDateInfin', 'Кожен місяць %DATE% числа');
define('RepeatEveryMonthDateTimes', 'Кожен місяць %DATE% числа %TIMES% разів');
define('RepeatEveryMonthDateUntil', 'Кожен місяць %DATE% числа по %UNTIL%');
define('RepeatMonthsDateInfin', 'Кожен %PERIOD% місяць %DATE% числа');
define('RepeatMonthsDateTimes', 'Кожен %PERIOD% місяць %DATE% числа %TIMES% раз(а)');
define('RepeatMonthsDateUntil', 'Кожен %PERIOD% місяць %DATE% числа по %UNTIL%');

define('RepeatEveryMonthWDInfin', 'Кожен %NUMBER% %DAY% місяця');
define('RepeatEveryMonthWDTimes', 'Кожен %NUMBER% %DAY% місяця %TIMES% разів');
define('RepeatEveryMonthWDUntil', 'Кожен %NUMBER% %DAY% місяця по %UNTIL%');
define('RepeatMonthsWDInfin', 'Кожен %PERIOD% місяця %NUMBER% %DAY%');
define('RepeatMonthsWDTimes', 'Кожен %PERIOD% місяця %NUMBER% %DAY% %TIMES% разів');
define('RepeatMonthsWDUntil', 'Кожен %PERIOD% місяця %NUMBER% %DAY% по %UNTIL%');

define('RepeatEveryYearDateInfin', 'Кожен рік %DATE%');
define('RepeatEveryYearDateTimes', 'Кожен рік %DATE%, %TIMES% разів');
define('RepeatEveryYearDateUntil', 'Кожен рік %DATE%, по %UNTIL%');
define('RepeatYearsDateInfin', 'Кожен %PERIOD% рік %DATE%');
define('RepeatYearsDateTimes', 'Кожен %PERIOD% рік %DATE% %TIMES% разів');
define('RepeatYearsDateUntil', 'Кожен %PERIOD% рік %DATE% по %UNTIL%');

define('RepeatEveryYearWDInfin', 'Кожен рік по %NUMBER% %DAY%');
define('RepeatEveryYearWDTimes', 'Кожен рік по %NUMBER% %DAY%, %TIMES% разів');
define('RepeatEveryYearWDUntil', 'Кожен рік по %NUMBER% %DAY%, по %UNTIL%');
define('RepeatYearsWDInfin', 'Кожен %PERIOD% рік по %NUMBER% %DAY%');
define('RepeatYearsWDTimes', 'Кожен %PERIOD% рік по %NUMBER% %DAY% %TIMES% разів');
define('RepeatYearsWDUntil', 'Кожен %PERIOD% рік по %NUMBER% %DAY% по %UNTIL%');

define('RepeatDescDay', 'день');
define('RepeatDescWeek', 'тиждень');
define('RepeatDescMonth', 'місяць');
define('RepeatDescYear', 'рік');

// webmail 4.5 contacts
define('WarningUntilDateBlank', 'Будь-ласка, вкажіть дату закінчення повторюваної події');
define('WarningWrongUntilDate', 'Будь-ласка, вкажіть дату закінчення повторюваної події пізнішою дати початку повторюваної події');

define('OnDays', 'По днях');
define('CancelRecurrence', 'Відмінити повторюваність');
define('RepeatEvent', 'Повторювати цю подію');

define('Spellcheck', 'Перевірка правопису');
define('LoginLanguage', 'Мова');
define('LanguageDefault', 'По замовчуванню');

// webmail 4.5.x new
define('EmptySpam', 'Почистити спам');
define('Saving', 'Зберігаю&hellip;');
define('Sending', 'Відсилаю&hellip;');
define('LoggingOffFromServer', 'Від\'єднання від серверу&hellip;');

// webmail 4.6
define('PROC_CANT_SET_MSG_AS_SPAM', 'Неможливо помітити листи , як спам');
define('PROC_CANT_SET_MSG_AS_NOTSPAM', 'Неможливо помітити листи , як не спам');
define('ExportToICalendar', 'Експортувати в iCalendar');
define('ErrorMaximumUsersLicenseIsExceeded', 'Ваш аккаунт вимкнено оскільки максимальну кількість користувачів дозволено ліцензією перевищено. Будь-ласка зв\'яжіться з адміністратором');
define('RepliedMessageTitle', 'Лист-відповідь');
define('ForwardedMessageTitle', 'Пересланий лист');
define('RepliedForwardedMessageTitle', 'Пересланий лист-відповідь');
define('ErrorDomainExist', 'Неможливо створити аккаунт , оскільки такий домен неіснує.Спочатку створіть відповідний домен');

// webmail 4.6.x or 4.7
define('RequestReadConfirmation', 'Request Return Confirmation');
define('FolderTypeDefault', 'Default');
define('ShowFoldersMapping', 'Let me use another folder as a system folder (e.g. use MyFolder as Sent Items)');
define('ShowFoldersMappingNote', 'For instance, to change Sent Items location from Sent Items to MyFolder, specify "Sent Items" in "Use for" dropdown of "MyFolder".');
define('FolderTypeMapTo', 'Use for');

define('ReminderSubjectStart', 'Reminder for: ');
define('ReminderEmailFriendlyName', 'Autoreminder');
define('ReminderEventCalendar', 'Calendar');
define('ReminderEmailExplanation','This message has come to your account %EMAIL% because you ordered event notification in your calendar %CALENDAR_NAME%');
define('ReminderOpenCalendar', 'Open calendar');

define('AddReminder', 'Remind me about this event');
define('AddReminderBefore', 'Remind me % before this event');
define('AddReminderAnd', 'and % before');
define('AddReminderAlso', 'and also % before');
define('AddMoreReminder', 'More reminders');
define('RemoveAllReminders', 'Remove all reminders');
define('ReminderNone', 'None');
define('ReminderMinutes', 'minutes');
define('ReminderHour', 'hour');
define('ReminderHours', 'hours');
define('ReminderDay', 'day');
define('ReminderDays', 'days');
define('ReminderWeek', 'week');
define('ReminderWeeks', 'weeks');
define('Allday', 'All day');

define('Folders', 'Folders');
define('NoSubject', 'No Subject');
define('SearchResultsFor', 'Search results for');

define('Back', 'Back');
define('Next', 'Next');
define('Prev', 'Prev');

define('MsgList', 'Messages');
define('Use24HTimeFormat', 'Use 24 hour time format');
define('UseCalendars', 'Use calendars');
define('Event', 'Event');
define('CalendarSettingsNullLine', 'No calendars');
define('CalendarEventNullLine', 'No events');
define('ChangeAccount', 'Change account');

define('TitleCalendar', 'Calendar');
define('TitleEvent', 'Event');
define('TitleFolders', 'Folders');
define('TitleConfirmation', 'Confirmation');

define('Yes', 'Yes');
define('No', 'No');

define('EditMessage', 'Edit Message');

define('AccountNewPassword', 'New password');
define('AccountConfirmNewPassword', 'Confirm new password');
define('AccountPasswordsDoNotMatch', 'Passwords do not match.');

define('ContactTitle', 'Title');
define('ContactFirstName', 'First name');
define('ContactSurName', 'Surname');
define('ContactNickName', 'Nickname');

define('CaptchaTitle', 'Captcha');
define('CaptchaReloadLink', 'reload');
define('CaptchaError', 'Captcha text is incorrect.');

define('WarningInputCorrectEmails', 'Please specify correct emails.');
define('WrongEmails', 'Incorrect emails:');

define('ConfirmBodySize1', 'Sorry, but text messages are max.');
define('ConfirmBodySize2', 'characters long. Everything beyond the limit will be truncated. Click "Cancel" if you want to edit the message.');
define('BodySizeCounter', 'Counter');
define('InsertImage', 'Insert Image');
define('ImagePath', 'Image Path');
define('ImageUpload', 'Insert');
define('WarningImageUpload', 'The file being attached is not an image. Please choose an image file.');

define('ConfirmExitFromNewMessage', 'Changes will be lost if you leave the page. Would you like to save draft before leaving the page?');

define('SensivityConfidential', 'Please treat this message as Confidential');
define('SensivityPrivate', 'Please treat this message as Private');
define('SensivityPersonal', 'Please treat this message as Personal');

define('ReturnReceiptTopText', 'The sender of this message has asked to be notified when you receive this message.');
define('ReturnReceiptTopLink', 'Click here to notify the sender.');
define('ReturnReceiptSubject', 'Return Receipt (displayed)');
define('ReturnReceiptMailText1', 'This is a Return Receipt for the mail that you sent to');
define('ReturnReceiptMailText2', 'Note: This Return Receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.');
define('ReturnReceiptMailText3', 'with subject');

define('SensivityMenu', 'Sensitivity');
define('SensivityNothingMenu', 'Nothing');
define('SensivityConfidentialMenu', 'Confidential');
define('SensivityPrivateMenu', 'Private');
define('SensivityPersonalMenu', 'Personal');

define('ErrorLDAPonnect', 'Can\'t connect to ldap server.');

define('MessageSizeExceedsAccountQuota', 'This message size exceeds your account quota.');
define('MessageCannotSent', 'The message cannot be sent.');
define('MessageCannotSaved', 'The message cannot be saved.');

define('ContactFieldTitle', 'Field');
define('ContactDropDownTO', 'TO');
define('ContactDropDownCC', 'CC');
define('ContactDropDownBCC', 'BCC');

// 4.9 
define('NoMoveDelete', 'Message(s) can\'t be moved to Trash. Most likely your message box is full. Should this unmoved message(s) be deleted?');

define('WarningFieldBlank', 'This field cannot be empty.');
define('WarningPassNotMatch', 'Passwords do not match, please check.');
define('PasswordResetTitle', 'Password recovery - step %d');
define('NullUserNameonReset', 'user');
define('IndexResetLink', 'Forgot password?');
define('IndexRegLink', 'Account Registration');

define('RegDomainNotExist', 'Domain does not exist.');
define('RegAnswersIncorrect', 'Answers are incorrect.');
define('RegUnknownAdress', 'Unknown email address.');
define('RegUnrecoverableAccount', 'Password recovery cannot be applied for this email address.');
define('RegAccountExist', 'This address is already used.');
define('RegRegistrationTitle', 'Registration');
define('RegName', 'Name');
define('RegEmail', 'e-mail address');
define('RegEmailDesc', 'For example, myname@domain.com. This information will be used to enter the system.');
define('RegSignMe', 'Remember me');
define('RegSignMeDesc', 'Do not ask for login and password on next login to the system on this PC.');
define('RegPass1', 'Password');
define('RegPass2', 'Repeat password ');
define('RegQuestionDesc', 'Please, provide two secret questions and answers which know only you. In case of password lost you can use these questions in order to recover the password.');
define('RegQuestion1', 'Secret question 1');
define('RegAnswer1', 'Answer 1');
define('RegQuestion2', 'Secret question 2');
define('RegAnswer2', 'Answer 2');
define('RegTimeZone', 'Time zone');
define('RegLang', 'Interface language');
define('RegCaptcha', 'Captcha');
define('RegSubmitButtonValue', 'Register');

define('ResetEmail', 'Please provide your email');
define('ResetEmailDesc', 'Provide emails address used for registration.');
define('ResetCaptcha', 'CAPTCHA');
define('ResetSubmitStep1', 'Send');
define('ResetQuestion1', 'Secret question 1');
define('ResetAnswer1', 'Answer');
define('ResetQuestion2', 'Secret question 2');
define('ResetAnswer2', 'Answer');
define('ResetSubmitStep2', 'Send');

define('ResetTopDesc1Step2', 'Providede email address');
define('ResetTopDesc2Step2', 'Please confirm correctness.');

define('ResetTopDescStep3', 'please specify below new password for your email.');

define('ResetPass1', 'New password');
define('ResetPass2', 'Repeat password');
define('ResetSubmitStep3', 'Send');
define('ResetDescStep4', 'Your password has been changed.');
define('ResetSubmitStep4', 'Return');

define('RegReturnLink', 'Return to login screen');
define('ResetReturnLink', 'Return to login screen');

// Appointments 
define('AppointmentAddGuests', 'Add guests');
define('AppointmentRemoveGuests', 'Cancel Meeting');
define('AppointmentListEmails', 'Enter email addresses separated by commas and press Save');
define('AppointmentParticipants', 'Participants');
define('AppointmentRefused', 'Refuse');
define('AppointmentAwaitingResponse', 'Awaiting response');
define('AppointmentInvalidGuestEmail', 'The following guest email addresses are invalid:');
define('AppointmentOwner', 'Owner');

define('AppointmentMsgTitleInvite', 'Invite to event.');
define('AppointmentMsgTitleUpdate', 'Event was modified.');
define('AppointmentMsgTitleCancel', 'Event was cancelled.');
define('AppointmentMsgTitleRefuse', 'Guest %guest% is refuse invitation');
define('AppointmentMoreInfo', 'More info');
define('AppointmentOrganizer', 'Organizer');
define('AppointmentEventInformation', 'Event information');
define('AppointmentEventWhen', 'When');
define('AppointmentEventParticipants', 'Participants');
define('AppointmentEventDescription', 'Description');
define('AppointmentEventWillYou', 'Will you participate');
define('AppointmentAdditionalParameters', 'Additional parameters');
define('AppointmentHaventRespond', 'Not responded yet');
define('AppointmentRespondYes', 'I will participate');
define('AppointmentRespondMaybe', 'Not sure yet');
define('AppointmentRespondNo', 'Will not participate');
define('AppointmentGuestsChangeEvent', 'Guests can change event');

define('AppointmentSubjectAddStart', 'You\'ve received invitation to event ');
define('AppointmentSubjectAddFrom', ' from ');
define('AppointmentSubjectUpdateStart', 'Modification of event ');
define('AppointmentSubjectDeleteStart', 'Cancellation of event ');
define('ErrorAppointmentChangeRespond', 'Unable to change appointment respond');
define('SettingsAutoAddInvitation', 'Add invitations into calendar automatically');
define('ReportEventSaved', 'Your event has been saved');
define('ReportAppointmentSaved', ' and notifications were send');
define('ErrorAppointmentSend', 'Can\'t send invitations.');
define('AppointmentEventName', 'Name:');

// End appointments

define('ErrorCantUpdateFilters', 'Can\'t update filters');

define('FilterPhrase', 'If there\'s %field header %condition %string then %action');
define('FiltersAdd', 'Add Filter');
define('FiltersCondEqualTo', 'equal to');
define('FiltersCondContainSubstr', 'containing substring');
define('FiltersCondNotContainSubstr', 'not containing substring');
define('FiltersActionDelete', 'delete message');
define('FiltersActionMove', 'move');
define('FiltersActionToFolder', 'to %folder folder');
define('FiltersNo', 'No filters specified yet');

define('ReminderEmailFriendly', 'reminder');
define('ReminderEventBegin', 'starts at: ');

define('FiltersLoading', 'Loading Filters...');
define('ConfirmMessagesPermanentlyDeleted', 'All messages in this folder will be permanently deleted.');

define('InfoNoNewMessages', 'There are no new messages.');
define('TitleImportContacts', 'Import Contacts');
define('TitleSelectedContacts', 'Selected Contacts');
define('TitleNewContact', 'New Contact');
define('TitleViewContact', 'View Contact');
define('TitleEditContact', 'Edit Contact');
define('TitleNewGroup', 'New Group');
define('TitleViewGroup', 'View Group');

define('AttachmentPending', 'Pending...');
define('AttachmentUploading', 'Uploading...');
define('AttachmentComplete', 'Complete.');
define('AttachmentCancelled', 'Cancelled.');
define('AttachmentStopped', 'Stopped.');
define('AttachmentsUpload', 'Attach Files');
define('AttachmentsUploadPadding', '34');

define('TestButton', 'TEST');
define('AutoCheckMailIntervalLabel', 'Autocheck interval');
define('AutoCheckMailIntervalDisableName', 'Disable');
define('ReportCalendarSaved', 'Calendar has been saved.');

define('ContactSyncError', 'Sync failed');
define('ReportContactSyncDone', 'Sync complete');

define('MobileSyncUrlTitle', 'Mobile sync URL');
define('MobileSyncLoginTitle', 'Mobile sync login');

define('QuickReply', 'Quick Reply');
define('SwitchToFullForm', 'Switch To Full Form');
define('SortFieldDate', 'Date');
define('SortFieldFrom', 'From');
define('SortFieldSize', 'Size');
define('SortFieldSubject', 'Subject');
define('SortFieldFlag', 'Flag');
define('SortFieldAttachments', 'Attachments');
define('SortOrderAscending', 'Ascending');
define('SortOrderDescending', 'Descending');
define('ArrangedBy', 'Arranged by');

define('MessagePaneToRight', 'The message pane is to the right of the message list, rather than below');

define('SettingsTabMobileSync', 'Mobile Sync');

define('MobileSyncContactDataBaseTitle', 'Mobile sync contact database');
define('MobileSyncCalendarDataBaseTitle', 'Mobile sync calendar database');
define('MobileSyncTitleText', 'If you\'d like to synchronize your SyncML-enabled handheld device with WebMail, you can use these parameters.<br />"Mobile Sync URL" specifies path to SyncML Data Synchronization server, "Mobile Sync Login" is your login on SyncML Data Synchronization Server and use your own password upon request. Also, some devices need to specify database name for contact and calendar data.<br />Use "Mobile sync contact database" and "Mobile sync calendar database" respectively.');
define('MobileSyncEnableLabel', 'Enable mobile sync');

define('SearchInputText', 'search');

define('AppointmentEmailExplanation','This message has come to your account %EMAIL% because you was invited to the event by %ORGANAZER%');

define('Searching', 'Searching&hellip;');

define('SaveMailInSentItems', 'Also save in Sent Items');
