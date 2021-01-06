<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

require_once __DIR__ . '/components/standard/Text.php';
require_once __DIR__ . '/components/standard/Navigation.php';

require_once __DIR__ . '/api/request/Request.php';
require_once __DIR__ . '/api/TelegramRequest.php';
require_once __DIR__ . '/api/VkRequest.php';
require_once __DIR__ . '/api/ViberRequest.php';
require_once __DIR__ . '/api/YandexRequest.php';
require_once __DIR__ . '/api/YandexImageRequest.php';
require_once __DIR__ . '/api/YandexSoundRequest.php';

require_once __DIR__ . '/components/button/types/TemplateButtonTypes.php';
require_once __DIR__ . '/components/button/types/AlisaButton.php';
require_once __DIR__ . '/components/button/types/SmartAppButton.php';
require_once __DIR__ . '/components/button/types/TelegramButton.php';
require_once __DIR__ . '/components/button/types/VkButton.php';
require_once __DIR__ . '/components/button/types/ViberButton.php';
require_once __DIR__ . '/components/button/Button.php';
require_once __DIR__ . '/components/button/Buttons.php';

require_once __DIR__ . '/components/card/types/TemplateCardTypes.php';
require_once __DIR__ . '/components/card/types/TelegramCard.php';
require_once __DIR__ . '/components/card/types/MarusiaCard.php';
require_once __DIR__ . '/components/card/types/AlisaCard.php';
require_once __DIR__ . '/components/card/types/SmartAppCard.php';
require_once __DIR__ . '/components/card/types/VkCard.php';
require_once __DIR__ . '/components/card/types/ViberCard.php';
require_once __DIR__ . '/components/card/Card.php';
require_once __DIR__ . '/components/image/Image.php';

require_once __DIR__ . '/components/nlu/Nlu.php';

require_once __DIR__ . '/components/sound/types/TemplateSoundTypes.php';
require_once __DIR__ . '/components/sound/types/TelegramSound.php';
require_once __DIR__ . '/components/sound/types/ViberSound.php';
require_once __DIR__ . '/components/sound/types/AlisaSound.php';
require_once __DIR__ . '/components/sound/types/VkSound.php';
require_once __DIR__ . '/components/sound/Sound.php';

require_once __DIR__ . '/controller/BotController.php';

require_once __DIR__ . '/core/mmApp.php';
require_once __DIR__ . '/core/Bot.php';
require_once __DIR__ . '/core/types/TemplateTypeModel.php';
require_once __DIR__ . '/core/types/Telegram.php';
require_once __DIR__ . '/core/types/SmartApp.php';
require_once __DIR__ . '/core/types/Marusia.php';
require_once __DIR__ . '/core/types/Alisa.php';
require_once __DIR__ . '/core/types/Viber.php';
require_once __DIR__ . '/core/types/Vk.php';

require_once __DIR__ . '/models/db/DB.php';
require_once __DIR__ . '/models/db/Sql.php';
require_once __DIR__ . '/models/db/Model.php';
require_once __DIR__ . '/models/ImageTokens.php';
require_once __DIR__ . '/models/SoundTokens.php';
require_once __DIR__ . '/models/UsersData.php';
