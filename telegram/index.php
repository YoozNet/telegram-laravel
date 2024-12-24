<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$update = new TelegramUpdates();
try {
    $chat_id = $update->chat_id ?? null;
    $text = $update->text ?? null;
    $data = $update->cb_data ?? null;
    $step = null;
    

    if($data == "back") {
        $backData = getBack($update->cb_data_chatid);
        if($backData['as'] == 'text') {
            $text = $backData['to'];
            $chat_id = $update->cb_data_chatid;
            if($backData['delete_message'] == true) {
                Telegram::api('deleteMessage',[
                    'message_id' => $update->cb_data_message_id,
                    'chat_id' => $chat_id
                ]);
            }
        } elseif($backData['as'] == 'data') {
            $data = $backData['to'];
        }

    }

    if(isset($text) && $text == "/start" || explode(" ", $text)[0] == "/start") {
        $existing_user = Database::select("YN_users", ["id"], "user_id = ?", [$chat_id]);
        if ($existing_user) {
            setUserStep($chat_id,'none');
            Telegram::api('sendMessage',[
                'reply_to_message_id' => $update->message_id,
                'chat_id' => $chat_id,
                'text' => "درود! 😅
    به ربات تلگرامی یوزنت خوش آمدید. با استفاده از دکمه‌های زیر می‌توانید با سرویس‌های VPN ما آشنا شوید و به صورت ناشناس در اینترنت گشت و گذار کنید ! 🥷🏻
    کافی است یکی از گزینه‌ها را انتخاب کنید و تجربه جدید خود را آغاز کنید! 👇😎",
                    'reply_markup' => [
                        'keyboard' => [
                            [
                                ['text' => '🗂 سرویس های من '],
                                ['text' => '⚜️ ثبت سرویس جدید '],
                            ],
                            [
                                ['text' => '👤 حساب کاربری'],
                                ['text' => '👝 کیف پول'],
                            ],
                            [
                                ['text' => '📞 پشتیبانی'],
                                ['text' => '🌐 ورود به سایت 🌐']
                            ]
                        ],
                        'resize_keyboard' => true,
                    ]
            ]); 
        } else {
            $parts = explode(" ", $text);
            $referral_code = isset($parts[1]) ? $parts[1] : null;
            createUser($chat_id);
            if ($referral_code) {
                $referrer = Database::select("YN_users", ["*"], "referral_id = ?", [$referral_code]);
                if ($referrer) {
                    # createUser($chat_id);
                    $referrer_chat_id = $referrer[0]['user_id'];
                    Database::update('YN_users',['referred_by'],[$referral_code],'user_id = ?',[$chat_id]);
                    
                    Telegram::api('sendMessage',[
                        'reply_to_message_id' => $update->message_id,
                        'chat_id' => $chat_id,
                        'text' => "شما به پیشنهاد یک دوست قابل‌اعتماد ، به خانواده یوزنت پیوستید!  😍🌷
    از حالا می‌توانید از خدمات حرفه‌ای کاهش پینگ ما لذت ببرید و با خیالی آسوده و ناشناس در اینترنت گشت‌وگذار کنید! 🥷🏻",
                        'reply_markup' => [
                        'keyboard' => [
                            [
                                ['text' => '🗂 سرویس های من '],
                                ['text' => '⚜️ ثبت سرویس جدید '],
                            ],
                            [
                                ['text' => '👤 حساب کاربری'],
                                ['text' => '👝 کیف پول'],
                            ],
                            [
                                ['text' => '📞 پشتیبانی'],
                                ['text' => ' 🌐 ورود به سایت 🌐']
                            ]
                        ],
                        'resize_keyboard' => true,
                        ]
                    ]);
                    Telegram::api('sendMessage',[
                        'chat_id' => $referrer_chat_id,
                        'text' => "تشکر ویژه از شما! 👏😊
    با معرفی یوزنت، نشون دادید که همیشه بهترین‌ها رو برای دوستاتون می‌خواید. 😌🌷
    حالا بقیه هم مثل شما می‌تونن لذت یه اینترنت حرفه‌ای و سریع رو تجربه کنند. 🎉
    حضور شما برای ما ارزشمند است. 🌟"
                    ]);
                }
            } else {
                Telegram::api('sendMessage',[
                    'reply_to_message_id' => $update->message_id,
                    'chat_id' => $update->chat_id,
                    'text' => "درود! 😅
        به ربات تلگرامی یوزنت خوش آمدید. با استفاده از دکمه‌های زیر می‌توانید با سرویس‌های VPN ما آشنا شوید و به صورت ناشناس در اینترنت گشت و گذار کنید ! 🥷🏻
        کافی است یکی از گزینه‌ها را انتخاب کنید و تجربه جدید خود را آغاز کنید! 👇😎",
                    'reply_markup' => [
                        'keyboard' => [
                            [
                                ['text' => '🗂 سرویس های من '],
                                ['text' => '⚜️ ثبت سرویس جدید '],
                            ],
                            [
                                ['text' => '👤 حساب کاربری'],
                                ['text' => '👝 کیف پول'],
                            ],
                            [
                                ['text' => '📞 پشتیبانی'],
                                ['text' => '🌐 ورود به سایت 🌐']
                            ]
                        ],
                        'resize_keyboard' => true,
                    ]
                ]);
            }
        }
    } elseif ($text == '👤 حساب کاربری') {
        setUserStep($chat_id,'none');
        setBackTo($chat_id,'/start','text');
        $userData = getUser($chat_id);
        $email = $userData['email'] ?? "تنظیم نشده";
        $group_id = $userData['group_id'];
        $group_id = App\Enum\UserGroupEnum::from($group_id)->getLabel();
        $discount = $userData['discount'];
        $cardNumber = adminCardNumber($chat_id);
        $cardInfo = splitCardNumber($cardNumber['card_number']) ?? "تنظیم نشده";
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "
ℹ️ اطلاعات حساب کاربری:
جی میل: ".$email."
شماره کارت پیشفرض برای پرداخت: ".$cardInfo."
گروه کاربری: ".$group_id."
تخفیف: ".$discount."%
            ",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🔹 تعیین شماره کارت پیشفرض', 'callback_data'=>'set_default_cardnumber'],
                    ],
                    [
                        ['text' => '📨 وب سرویس', 'callback_data'=>'web_service'],
                        ['text' => '➕ دعوت از دوستان', 'callback_data'=>'invite_friends'],
                    ],
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);
    } elseif ($text == "👝 کیف پول") {

        setUserStep($chat_id,'none');
        $userData = getUser($chat_id);

        $cardBanks = getCardsBank($userData['id']);
        $wallet = $userData['irr_wallet'] ?? 0.00;
        $group_id = $userData['group_id'];
        $config = GetConfig();
        $YC_Price = $config['yc_price'];

        $addBalance = "AddBalance";
        if ($group_id < 1 or count($cardBanks) < 1) {
            $addBalance = "bankCards";
        }

        $formattedWallet = formatWallet($wallet);
        $walletInToman = $formattedWallet * $YC_Price;
        $formattedWalletInToman = number_format($walletInToman, 0, '', ',');


        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "🧳 کیف پول شما شامل سه بخش اصلی است:

💰 **افزایش اعتبار:** می‌توانید اعتبار خود را از 10,000 تا 2,000,000 تومان افزایش دهید!🥹

📊 **صورتحساب‌ها:** مشاهده صورتحساب های شما.

💳 ** کارت بانکی  ** : شما برای اینکه بتوانید کیف پول خود را شارژ کنید نیاز هست ابتدا کارت بانکی خود را تایید کنید و بعد از تایید میتوانید کارت تایید شده خود را مشاهده کنید و در صورت نیاز حذفش کنید!

اعتبار اکانت شما: `". $formattedWallet ."` یوزکوین  (هر یوزکوین معادل **".$YC_Price." تومان** است.)
👉 بنابراین موجودی شما معادل " . $formattedWalletInToman . " تومان می‌باشد! 💸

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎
            ",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '📊 صورتحساب ها', 'callback_data'=>'Invoices'],
                        ['text' => '💰 افزایش اعتبار', 'callback_data'=>$addBalance],
                    ],
                    [
                        ['text' => '💳 کارت بانکی', 'callback_data'=>'bankCards'],
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);
    } elseif ($text == "🌐 ورود به سایت 🌐"){
        $link = LoginToken($chat_id);
        setUserStep($chat_id,'none');
        setBackTo($chat_id,'/start','text');
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "یک لینک ورود به سایت برای شما ایجاد شد! 😍
              لطفا توجه داشته باشید که این لینک تنها برای 15 دقیقه فعال خواهد بود. پس از ورود، لینک منقضی خواهد شد و شما برای ورود بعدی خود نیاز به دریافت مجدد لینک از ربات خواهید داشت. همچنین هر لینک تنها یکبار قابل استفاده است!🤗",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🔹 ورود به سایت ', 'url' => $link],
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ],
                ],
            ]
        ]);
    }

    if ($data == "Profile") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'/start','text');
        $userData = getUser($update->cb_data_chatid);
        $email = $userData['email'] ?? "تنظیم نشده";
        $group_id = $userData['group_id'];
        $group_id = App\Enum\UserGroupEnum::from($group_id)->getLabel();
        $discount = $userData['discount'];
        $cardNumber = adminCardNumber($update->cb_data_chatid);
        $cardInfo = $cardNumber['card_number'] ?? "تنظیم نشده";
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "
ℹ️ اطلاعات حساب کاربری:
ایمیل: ".$email."
شماره کارت پیشفرض برای پرداخت: ".splitCardNumber($cardInfo)."
گروه کاربری: ".$group_id."
تخفیف: ".$discount."%
            ",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🔹 تعیین شماره کارت پیشفرض', 'callback_data'=>'set_default_cardnumber'],
                    ],
                    [
                        ['text' => '📨 وب سرویس', 'callback_data'=>'web_service'],
                        ['text' => '➕ دعوت از دوستان', 'callback_data'=>'invite_friends'],
                    ],
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);
    } elseif ($data == "wallet") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'/start','text');
        
        $userData = getUser($update->cb_data_chatid);

        $cardBanks = getCardsBank($userData['id']);
        $wallet = $userData['irr_wallet'] ?? 0.00;
        $group_id = $userData['group_id'];
        $config = GetConfig();
        $YC_Price = $config['yc_price'];

        $formattedWallet = formatWallet($wallet);
        $walletInToman = $formattedWallet * $YC_Price;
        $formattedWalletInToman = number_format($walletInToman, 0, '', ',');
        Telegram::api('deleteMessage',[
            'message_id' => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid
        ]);

        Telegram::api('sendMessage',[
            'chat_id' => $update->cb_data_chatid,
            'text' => "🧳 کیف پول شما شامل سه بخش اصلی است:

💰 **افزایش اعتبار:** می‌توانید اعتبار خود را از 10,000 تا 2,000,000 تومان افزایش دهید!🥹

📊 **صورتحساب‌ها:** مشاهده صورتحساب های شما.

💳 ** کارت بانکی  ** : شما برای اینکه بتوانید کیف پول خود را شارژ کنید نیاز هست ابتدا کارت بانکی خود را تایید کنید و بعد از تایید میتوانید کارت تایید شده خود را مشاهده کنید و در صورت نیاز حذفش کنید!

اعتبار اکانت شما: `". $formattedWallet ."` یوزکوین  (هر یوزکوین معادل **".$YC_Price." تومان** است.)
👉 بنابراین موجودی شما معادل " . $formattedWalletInToman . " تومان می‌باشد! 💸

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '📊 صورتحساب ها', 'callback_data'=>'Invoices'],
                        ['text' => '💰 افزایش اعتبار', 'callback_data'=>$addBalance],
                    ],
                    [
                        ['text' => '💳 کارت بانکی', 'callback_data'=>'bankCards'],
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);
    } elseif ($data == "Invoices") {
        $userData = getUser($update->cb_data_chatid);
        $invoiceList = getUserInvoices($userData['id'],2);
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "debug : ".json_encode($invoiceList,128|256),
        ]);
    } elseif ($data == "web_service") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'Profile','data');
        $userData = getUser($update->cb_data_chatid);
        $ip = $userData['ip_address'] ?? "تنظیم نشده";
        $api_token = $userData['api_token'] ?? "تنظیم نشده";
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "در این بخش، ارتباطی بین کسب و کار شما و توسعه‌دهندگانی که می‌خواهند از API ما استفاده کنند، برقرار می‌کنید. با ارائه توکن اختصاصی و تعریف آی‌پی سرور خود، آنها می‌توانند به API ما متصل شوند. ما به توسعه‌دهندگان اجازه می‌دهیم با داده‌های ما کار کنند و از قابلیت‌های API استفاده کنند.

در این بخش، شما می‌توانید کسب و کار خود را با توسعه‌دهندگانی که می‌خواهند روند اتصال و اتصال به سیستم‌های خود را مشاهده کنند، با کلیک بر روی دکمه مشاهده داکیومنت ارتباط برقرار کنید.

آی پی متصل به توکن شما : `$ip`
توکن شما : 
```
$api_token
```
",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'مشاهده داکیومنت', 'url' => 'https://documenter.getpostman.com/view/19387923/2sA3sAfmZ6'],
                    ],
                    [
                        ['text' => 'تنظیم آی پی سرور', 'callback_data'=>'set_ip_address'],['text' => 'بازگشت ◀️', 'callback_data'=>'Profile'],
                    ]
                ],
            ]
        ]);
    } elseif ($data == "set_ip_address") {
        setUserStep($update->cb_data_chatid,'set_ip_address_1');
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "
لطف کنید IP مورد نظر را ارسال کنید
            ",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'web_service'],
                    ]
                ],
            ]
        ]);
    } elseif ($data == "invite_friends") {
        setBackTo($update->cb_data_chatid,'Profile','data');
        $userData = getUser($update->cb_data_chatid);
        $referral = $userData['referral_id'];
        $referral_count = count(Database::select("YN_users", ["id"], "referred_by = ?", [$referral]));
        $link = "https://t.me/". $_ENV['TELEGRAM_BOT_USERNAME'] ."?start=$referral";
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "میتوانید از طریق ارسال و به اشتراک گذاری لینک، دعوت دیگران به این سایت را داشته باشید. با هر خریدی که از لینک شما انجام شود، شما می‌توانید 0.1 درصد پورسانت دریافت کنید. همچنین، با جذب افراد جدید و دعوت آن‌ها برای استفاده از این سایت می‌توانید درآمد رفرال نیز کسب کنید.

تعداد رفرال های دریافتی : `$referral_count`
لینک دعوت شما : 
```
$link
```
",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'کپی لینک', 'copy_text' => ['text' => $link]],
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'Profile'],
                    ]
                ],
            ]
        ]);

    } elseif ($data == "set_default_cardnumber") {
        setBackTo($update->cb_data_chatid,'Profile','data');
        $activeBanks = getAdminCards();
        if ($activeBanks == []) {
            Telegram::api('editMessageText',[
                'chat_id' => $chat_id,
                'text' => "
کارت بانکی فعالی وجود ندارد
                ",
            ]);
        } else {
            $activeCardNumber = adminCardNumber($update->cb_data_chatid);
            $inline_keyboard = [];
            foreach ($activeBanks as $cardData) {
                $is_setted = ($cardData['card_number'] == $activeCardNumber['card_number']) ? "✅" : "تنظیم";
                $inline_keyboard[] = [
                    ['text' => $is_setted, 'callback_data'=>'set_default_card_'. $cardData['id']],
                    ['text' => getBankName($cardData['bank']), 'callback_data'=>'set_default_card_'. $cardData['id']],
                    ['text' => splitCardNumber($cardData['card_number']), 'callback_data'=>'set_default_card_'. $cardData['id']],
                ];
            }
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'Profile'],
            ];
            Telegram::api('editMessageText',[
                'chat_id' => $update->cb_data_chatid,
                "message_id" => $update->cb_data_message_id,
                'text' => "در بخش شماره کارتی را انتخاب کنید. در پرداخت ها شما باید واریزی های خود را به این کارت انجام دهید; در صورتی که پرداختی شما با کارت انتخابی مغایرت داشته باشد، تراکنش شما رد میشود",
                'reply_markup' => [
                    'inline_keyboard' => $inline_keyboard,
                ]
            ]);
        }
    } elseif ($data == "AddBalance") {
        setBackTo($update->cb_data_chatid,'👝 کیف پول','text');
        $userData = getUser($update->cb_data_chatid);
        $cardBanks = getCardsBank($userData['id']);
        $group_id = $userData['group_id'];
        $addBalance = "AddBalance";
        if ($group_id < 1 or count($cardBanks) < 1) {
            die();
        }
        setUserStep($update->cb_data_chatid,'addBalance_1');
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "لطفا مبلغی که قصد دارید ، اعتبار شما به اندازه آن بیشتر شود بصورت تومان وارد کنید! 😅 
لطفاً توجه داشته باشید که این مبلغ باید در محدوده بین  10,000 تا 2,000,000  تومان باشد! ",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);
    } elseif (isset($data) && preg_match("/set_default_card_(.*)/",$data,$result)) {
        setBackTo($update->cb_data_chatid,'Profile','data');
        $selectedCardId = $result[1];
        $existingCard = adminCardNumber($update->cb_data_chatid);

        if ($existingCard && $existingCard['id'] == $selectedCardId) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "این شماره کارت قبلاً به‌عنوان کارت پیش‌فرض تنظیم شده است. ⛔️",
                'show_alert' => true,
            ]);
            return;
        }

        Database::update('YN_users',['admin_bank_card_id'],[$result[1]],'user_id = ?',[$update->cb_data_chatid]);

        $inline_keyboard[] = [
            ['text' => 'بازگشت ◀️', 'callback_data'=>'set_default_cardnumber'],
        ];
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "شماره کارت شما با موفقیت تنظیم شد ✅
برای ادامه بر روی بازگشت کلیک کنید! 👇😎",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    }


    ## Step's ## <-------------------------
    if (!is_null($chat_id)) {
        $step = getUserStep($chat_id);
    }
    if(!is_null($update->cb_data_chatid)) {
        $step = getUserStep($update->cb_data_chatid);
    }

    if ($step == 'set_ip_address_1') {
        if(!filter_var($text,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
            $response = "این یک IP نیست";
        } else {
            setUserStep($chat_id,'none');
            setUserIP($chat_id,$text);
            $response = "تنظیم شد";
        }
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => $response,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'web_service'],
                    ]
                ],
            ]
        ]);
    } elseif ($step == 'addBalance_1') {
        $inline_keyboard = [];
        if (!is_numeric($text) || $text < 10000 || $text > 2000000) {
            $response = "لطفاً توجه نمایید که مبلغ مورد نظر برای افزایش اعتبار باید بین ۱۰,۰۰۰ تا ۲,۰۰۰,۰۰۰ تومان باشد! 💵✨ 
لطفا مبلغ مورد نظر خود را مجدداً ارسال کنید! 🙏😊";
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
            ];
        } else {
            setBackTo($chat_id,'addBalance','data');
            setUserStep($chat_id,'addBalance_2');
            setUserTmp($chat_id,'addBalance_amount',$text);
            $userID = getUser($chat_id)['id'];
            setUserTmp($chat_id,'user_id',$userID);
            $cardBanks = getCardsBank($userID);
            $response = "لطفاً کارتی که قصد دارید وجه را با آن پرداخت کنید انتخاب کنید 💳";
            foreach ($cardBanks as $cardData) {
                $inline_keyboard[] = [
                    ['text' => splitCardNumber($cardData['card_number'])." (".getBankName($cardData['bank']).")", 'callback_data'=>'addBalance_select_'. $cardData['id']],
                ];
            }
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
            ];
        }
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => $response,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($step == 'addBalance_2') {
        $id = preg_match("/addBalance_select_(.*)/",$data,$result);
        if(isset($result[1])) {
            $data = getCardById($result[1]);
            setUserTmp($update->cb_data_chatid,'addBalance_userCardId',$result[1]);
            $cardNumber = adminCardNumber($update->cb_data_chatid);
            $cardInfo = $cardNumber['card_number'] ?? null;
            $iban = null;
            $bank = null;
            $fullname = null;
            if(!is_null($cardInfo)) {
                $cardBankNumber = $cardInfo;
                $cardBankImage = $cardNumber['card_image_file_id'];
                $cardBankId = $cardNumber['id'];
                $iban = $cardNumber['iban'];
                $bank = getBankName($cardNumber['bank']);
                $fullname = $cardNumber['first_name'] . " " . $cardNumber['last_name'];
            } else {
                $findAsName = getBankByName($data['bank']);
                if(count($findAsName) > 0) {
                    $randKey = array_rand($findAsName);
                    $cardBankNumber = $findAsName[$randKey]['card_number'];
                    $cardBankImage =  $findAsName[$randKey]['card_image_file_id'];
                    $cardBankId =  $findAsName[$randKey]['id'];
                    $iban = $findAsName[$randKey]['iban'];
                    $bank = getBankName($findAsName[$randKey]['bank']);
                    $fullname = $findAsName[$randKey]['first_name'] . " " . $findAsName[$randKey]['last_name'];
                } else {
                    $adminCards = getAdminCards();
                    $randKey = array_rand($adminCards);
                    $cardBankNumber = $adminCards[$randKey]['card_number'];
                    $cardBankImage =  $adminCards[$randKey]['card_image_file_id'];
                    $cardBankId =  $adminCards[$randKey]['id'];
                    $iban = $adminCards[$randKey]['iban'];
                    $bank = getBankName($adminCards[$randKey]['bank']);
                    $fullname = $adminCards[$randKey]['first_name'] . " " . $adminCards[$randKey]['last_name'];
                }
            }
            setUserTmp($update->cb_data_chatid,'addBalance_cardBankNumber',$cardBankNumber);
            setUserTmp($update->cb_data_chatid,'addBalance_cardBankId',$cardBankId);
            setUserStep($update->cb_data_chatid,'addBalance_3');

            $amount = getUserTmp($update->cb_data_chatid,'addBalance_amount');

            $tax = GenerateTaxPrice($amount);
            setUserTmp($update->cb_data_chatid,'Tax_value',$tax);
            
            $total = $amount + $tax;
            $amount_format = number_format($total, 0, '', ',');
            $card_number_format = splitCardNumber($cardBankNumber);

            $config = GetConfig();
            $YC_Price = $config['yc_price'];

            $YC_COIN = displayNumber($total / $YC_Price,true);
            setUserTmp($update->cb_data_chatid,'YC_value',$YC_COIN);

            Telegram::api('sendPhoto',[
                'chat_id' => $update->cb_data_chatid,
                'photo' => "https://maindns.space/file/" . $cardBankImage,
                'caption' => "💰 لطفا مبلغ : ` $amount_format ` تومان معادل ( ".$YC_COIN." ) یوزکوین
💳 به شماره کارت : 
` $card_number_format `
💳 به شماره شبا : 
` $iban `
💎 به نام :  $bank ( ".$fullname." )
واریز بفرمایید و سپس اسکرین شات واریزی را برای ما ارسال کنید!😅

‼️ لطفا با کارتی که تایید کردید واریز بفرمایید تا تراکنش شما تایید شود 😊",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'کپی شماره کارت', 'copy_text' => ['text' => $cardBankNumber]],
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
                        ]
                    ],
                ]
            ]);
        } else {
            setUserStep($update->cb_data_chatid,'none');
        }
    } elseif ($step == 'addBalance_3') {
        if(isset($update->photo_file_id)) {
            $tmp = getAllUserTmp($chat_id);
            $adminCardNumber = $tmp['addBalance_cardBankNumber'];
            $adminCardId = $tmp['addBalance_cardBankId'];
            $clientCardId = $tmp['addBalance_userCardId'];
            $amount = $tmp['addBalance_amount'];
            $tax = $tmp['Tax_value'];
            $yc_amount = $tmp['YC_value'];
            $userid = $tmp['user_id'];

            $invoiceId = Database::create('YN_invoices',
            ['user_id','admin_bank_card_id','bank_card_id','amount','tax_avoidance','yc_amount','currency','status','file_id','paid_at','created_at', 'updated_at'],
                [
                    $userid,
                    $adminCardId,
                    $clientCardId,
                    $amount,
                    $tax,
                    $yc_amount,
                    "IRT",
                    App\Enum\InvoiceStatus::WAITING_CONFIRMATION->value,
                    $update->photo_file_id,
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")]
            );
            $webservice = API::sendInvoice(["user_id" => $userid,"invoice_id" => $invoiceId]);
            if ($webservice['status'] == true) {
                Telegram::api('sendMessage',[
                    'chat_id' => $chat_id,
                    'text' => "پرداخت شما با موفقیت به واحد مالی ارسال شد ، بعد از بررسی نتیجه را به شما اطلاع می‌دهیم.
        با تشکر از شما",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
                            ]
                        ],
                    ]
                ]);
            } 
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "لطفا در قالب عکس ارسال کنید",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
                        ]
                    ],
                ]
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
}
