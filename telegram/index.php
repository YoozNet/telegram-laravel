<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$update = new TelegramUpdates();
try {
    $chat_id = $update->chat_id ?? null;
    $text = $update->text ?? '';
    $data = $update->cb_data ?? '';
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
            if($backData['new_message'] == true) {
                Telegram::api('deleteMessage',[
                    'message_id' => $update->cb_data_message_id,
                    'chat_id' => $update->cb_data_chatid
                ]);
                $sendMessage = Telegram::api('sendMessage',[
                    'chat_id' => $update->cb_data_chatid,
                    'text' => "لطفا چند لحظه صبر کنید..."
                ]);
                $update->cb_data_message_id = json_decode($sendMessage->getContents(),1)['result']['message_id'];
            }

        }

    }

    if($text == "/start" || $text != '' && explode(" ", $text)[0] == "/start") {
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
                                ['text' => '🗂 سرویس های من'],
                                ['text' => '⚜️ ثبت سرویس جدید'],
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
                                ['text' => '🗂 سرویس های من'],
                                ['text' => '⚜️ ثبت سرویس جدید'],
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
                                ['text' => '🗂 سرویس های من'],
                                ['text' => '⚜️ ثبت سرویس جدید'],
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
    } elseif ($text == '🗂 سرویس های من') {
        setUserStep($chat_id,'none');
        setBackTo($chat_id,'/start','text');
        $getUser = getUser($chat_id);
        $services = getUserService ($getUser['id']);
        $serviceList = GetAllServices();
        $inline_keyboard = [];
        $inline_keyboard[] = [
            ['text' => '-', 'callback_data'=>'open_service'],
            ['text' => 'وضعیت', 'callback_data'=>'open_service'],
            ['text' => 'زمان باقیمانده', 'callback_data'=>'open_service'],
            ['text' => 'نوع', 'callback_data'=>'open_service'],
            ['text' => 'شناسه', 'callback_data'=>'open_service'],
        ];
        foreach ($services as $service) {
            $server_id = $service['server_id'];
            $type = serverToType($server_id);
            $expired_at = strtotime($service['expired_at']);
            $days_left = round(($expired_at - time()) / 86400);
            $status = App\Enum\ServiceStatus::from($service['status'])->text();
            $inline_keyboard[] = [
                ['text' => '🔍', 'callback_data'=>'open_service_'.$type.'_'.$service['id']],
                ['text' => $status, 'callback_data'=>'open_service_'.$type.'_'.$service['id']],
                ['text' => $days_left.' روز', 'callback_data'=>'open_service_'.$type.'_'.$service['id']],
                ['text' => $serviceList[$type]['name'], 'callback_data'=>'open_service_'.$type.'_'.$service['id']],
                ['text' => $service['id'], 'callback_data'=>'open_service_'.$type.'_'.$service['id']],
            ];
        }
        $inline_keyboard[] = [
            ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
        ];
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "شما در این بخش لیست سرویس های خود را مشاهده میکنید و میتوانید آنهارا مدیریت کنید",
            'reply_to_message_id' => $update->message_id,
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard
            ]
        ]);

    } elseif (isset($data) && preg_match('/open_service_(.*)_(.*)/',$data,$result)) {
        $type = $result[1];
        $service_id = $result[2];
        $serviceData = getService($service_id);
        $status = $serviceData['status'];
        if(!in_array($status,[2,5,6])) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "⛔️ متاسفانه، شما مجاز به مدیریت این سرویس نیستید.",
                'show_alert' => true,
            ]);
            return;
        } else {
            setBackTo($update->cb_data_chatid,'🗂 سرویس های من','text');

            $main_traffic = $serviceData['main_traffic'];
            $data_usage = $serviceData['data_usage'];
            $subscribe_uuid = $serviceData['subscribe_uuid'];
            $expired_at = $serviceData['expired_at'];
            
            $config = GetConfig();
            $link = $config['uuid-subscripe'] . $subscribe_uuid;

            $t = "شما درحال مدیریت اشتراک ( $service_id ) هستید! 😎 \n ━━━━━━━━━━ \n";
            $t .= "🔗 لینک جهت اتصال : \n ``` $link ``` \n";
            $t .= "📅 انقضا: \n $expired_at \n";

            $total_traffic = 0;
            $status_text = App\Enum\ServiceStatus::from($serviceData['status'])->text();
            if ($type == "unlimited") {
                $total_traffic = $main_traffic * 30;
                $total_usage = $serviceData['total_usage'];
                $t .= "📊 ترافیک: \n $total_usage GB / $total_traffic GB \n";
                $t .= "🌞 حجم مصرف امروز : \n $data_usage GB \n"; 
                $t .= "🪫 باقی مانده: ".$total_traffic - $total_usage ." GB \n";
            } else {
                $traffic = $serviceData['traffic'];

                if ($type == "tunnel") {
                    $total_traffic = $traffic + $main_traffic;
                    $total_traffic *= 2;
                } else {
                    $total_traffic = $traffic + $main_traffic;
                }

                $t .= "📊 ترافیک: \n $data_usage GB / $total_traffic GB \n";
                $t .= "🪫 باقی مانده: ".$total_traffic - $data_usage ." GB \n";
            }

            $t .= "📶 وضعیت: $status_text \n ━━━━━━━━━━ \n \n برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎";

            Telegram::api('editMessageText',[
                'chat_id' => $update->cb_data_chatid,
                'message_id' => $update->cb_data_message_id,
                'text' => $t,
                'reply_to_message_id' => $update->message_id,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                        ]
                    ],
                ]
            ]);
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
        $cardInfo = isset($cardNumber['card_number']) && $cardNumber['card_number'] != null ? splitCardNumber($cardNumber['card_number'])  : "تنظیم نشده";
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "
ℹ️ اطلاعات حساب کاربری: 
شناسه مشتری : ".$userData['id']."
ایمیل: ".$email."
شماره کارت پیشفرض برای پرداخت: ".$cardInfo."
گروه کاربری: ".$group_id."
تخفیف: ".$discount."%

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_to_message_id' => $update->message_id,
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
        setBackTo($chat_id,'/start','text');
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

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_to_message_id' => $update->message_id,
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
            'reply_to_message_id' => $update->message_id,
            'chat_id' => $chat_id,
            'text' => "یک لینک ورود به سایت برای شما ایجاد شد! 😍
              لطفا توجه داشته باشید که این لینک تنها برای 15 دقیقه فعال خواهد بود. پس از ورود، لینک منقضی خواهد شد و شما برای ورود بعدی خود نیاز به دریافت مجدد لینک از ربات خواهید داشت. همچنین هر لینک تنها یکبار قابل استفاده است!🤗
              
              برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
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
    } elseif ($text == "📞 پشتیبانی"){
        setUserStep($chat_id,'none');
        setBackTo($chat_id,'/start','text');
        Telegram::api('sendMessage',[
            'reply_to_message_id' => $update->message_id,
            'chat_id' => $chat_id,
            'text' => "خوش آمدید به بخش پشتیبانی! 👋 

📩 برای مشکلات و سوالات خود، تیکت ارسال کنید.

❓ سوالات رایج را بررسی کنید تا سریع‌تر به پاسخ‌ها برسید.

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'تیکت 📬', 'callback_data'=>'Tickets'],
                        ['text' => 'سوالات رایج ❓', 'callback_data'=>'faqs'],
                    ],
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ],
                ],
            ]
        ]);
    } elseif($text == '⚜️ ثبت سرویس جدید') {
        setUserStep($chat_id,'none');
        setBackTo($chat_id,'/start','text');
        $serviceList = GetAllServices();
        $serviceDetail = "در این بخش می‌توانید نوع سرویسی که قصد دارید تهیه کنید را مشخص کنید ! 😊 \n\n";
        $inline_keyboard = [];
        $emojiList = ['🔴', '🟠', '🟡', '🟢', '🔵', '🟣'];
        $randomEmojiIndex = array_rand($emojiList,3);
        $c_for_randemoji = 0;
        foreach($serviceList as $service) {
            // $randomEmojiIndex = array_rand($emojiList);
            $randomEmoji = $emojiList[$randomEmojiIndex[$c_for_randemoji]];
            $c_for_randemoji += 1;
            $servicePrice = getServicePrice($chat_id,$service['type']);
            $vip = $service['special'] == true ? "** ( پیشنهادی یوزنت ) **" : '';
            $serviceDetail .= $randomEmoji." ". $service['name'] ." $vip
- قیمت هر گیگ : ". $servicePrice['yc'] ." یوزکوین معادل ( ". $servicePrice['irt'] ." ) تومان
    - مزایا : 
". implode("\n",$service['pros']). "
    - معایب : 
". implode("\n",$service['cons']) ."
➖➖➖➖➖
";
            $inline_keyboard[] = ['text' => $randomEmoji." ". $service['name'], 'callback_data'=> 'order_service_'.$service['type']];
        }
        $inline_keyboard[] = ['text' => 'بازگشت ◀️', 'callback_data'=>'back'];
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => $serviceDetail . "\n برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_to_message_id' => $update->message_id,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => array_chunk($inline_keyboard,2),
            ]
        ]);

        $userTmp = getAllUserTmp($chat_id);
        if(isset($userTmp['waitpay_for_service']) && $userTmp['waitpay_for_service'] == 1) {
            $userData = getUser($chat_id);
            $service_type = $userTmp['service_type'];
            $service_size = $userTmp['service_size'];

            $price = getServicePrice($chat_id,$service_type);
            $price_yc = $price['yc'] * $service_size;

            if($userData['irr_wallet'] >= $price_yc) {
                setUserTmp($chat_id,'waitpay_for_service',0);
                $t = "آخرین سفارش شما به دلیل عدم موجودی نهایی نشده است. ⚠️ \t";
                $size = "";
                if ($service_type == "unlimited") {
                    $unlimitedPlans = $serviceList[$service_type]['plans'];
                    $selectedPlanName = "";
                    foreach ($unlimitedPlans as $planId => $plan) {
                        if ($plan['data_total'] == $service_size) {
                            $selectedPlanName = $plan['name'];
                            $size = $planId;
                            break;
                        }
                    }

                    $t .= "شما قصد تهیه پلن $selectedPlanName از سرویس ".$serviceList[$service_type]['name']." را داشتید.";

                } else {
                    $t .= "شما قصد تهیه $service_size گیگابایت حجم از سرویس ".$serviceList[$service_type]['name']." را داشتید.";
                    $size = $userTmp['service_size'];
                }
                $t .= "\n \n🎗 هم اکنون اعتبار حساب کاربری شما برابر با مبلغ این سفارش است ، آیا مایل به نهایی کردن این سفارش هستید؟ 🤔✨";
                Telegram::api('sendMessage',[
                    'chat_id' => $chat_id,
                    'text' => $t,
                    'reply_to_message_id' => $update->message_id,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => 'تکمیل سفارش ✅', 'callback_data'=>'order_service2_'.$userTmp['service_orderby'].'_'.$userTmp['order_service_type'].'_'.$size],
                            ],
                        ],
                    ]
                ]); 
                return;
            }
        }
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
        $cardInfo = isset($cardNumber['card_number']) && $cardNumber['card_number'] != null ? splitCardNumber($cardNumber['card_number'])  : "تنظیم نشده";
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "
ℹ️ اطلاعات حساب کاربری: 
شناسه مشتری : ".$userData['id']."
ایمیل: ".$email."
شماره کارت پیشفرض برای پرداخت: ".$cardInfo."
گروه کاربری: ".$group_id."
تخفیف: ".$discount."%
            
برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
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

آی پی متصل به توکن شما : $ip",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'کپی کردن توکن', 'copy_text' => ['text' => $api_token]],
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
        setUserStep($update->cb_data_chatid,'none');
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
        setUserStep($update->cb_data_chatid,'none');
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
                $is_setted = ($activeCardNumber != null && $cardData['card_number'] == $activeCardNumber['card_number']) ? "✅" : "تنظیم";
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
    } elseif ($data == "wallet") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'/start','text');
        
        $userData = getUser($update->cb_data_chatid);

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

        Telegram::api('deleteMessage',[
            'message_id' => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid
        ]);

        Telegram::api('sendMessage',[
            'chat_id' => $update->cb_data_chatid ?? $chat_id,
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
        setUserStep($update->cb_data_chatid,'none');
        $userData = getUser($update->cb_data_chatid);
        $invoiceList = getUserInvoices($userData['id'],10);
        if (empty($invoiceList)) {
            Telegram::api('editMessageText', [
                'chat_id' => $update->cb_data_chatid,
                'message_id' => $update->cb_data_message_id,
                'text' => "فاکتوری برای شما تولید نشده است! برای ادامه، لطفاً بر روی ( بازگشت ◀️ ) کلیک کنید و سپس  ( افزایش اعتبار ) را انتخاب کنید تا بتوانید یک فاکتور جدید ایجاد کنید.",
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
                        ]
                    ],
                ]
            ]);
            return; 
        }
        $inline_keybaord = [];
        if (!empty($invoiceList)) {
            $inline_keyboard[] = [
                ['text' => 'جزییات', 'callback_data'=>'invoice_status'],
                ['text' => 'وضعیت', 'callback_data'=>'invoice_status'],
                ['text' => 'مبلغ', 'callback_data'=>'invoice_amount'],
                ['text' => 'شناسه', 'callback_data'=>'invoice_title'],
            ];
        }
        foreach($invoiceList as $invoices) {
            $invoiceId = $invoices['id'] ?? 'error';
            $invoiceAmount = $invoices['amount'] ?? 'error';
            $invoiceStatus = $invoices['status'] ?? 'error';
            $formattedInvoiceAmount = formatWallet($invoiceAmount);
            $invoiceStatusLabel = App\Enum\InvoiceStatus::from($invoiceStatus)->text();

            $inline_keyboard[] = [
                ['text' => '🔎', 'callback_data' => 'invoice_data_'.$invoiceId],
                ['text' => $invoiceStatusLabel, 'callback_data' => 'invoice_data_'.$invoiceId],
                ['text' => number_format($formattedInvoiceAmount, 0, '', ',') . " ت", 'callback_data' => 'invoice_data_'.$invoiceId],
                ['text' => $invoiceId, 'callback_data' => 'invoice_data_'.$invoiceId],
            ];
        }
        $inline_keyboard[] = [
            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
        ];

        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "در این بخش شما لیست فاکتورهای خود را مشاهده می‌کنید و می‌توانید آنها را مدیریت کنید.",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($data == "bankCards") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'wallet','data');
        $userData = getUser($update->cb_data_chatid);
        $BankCardList = getUserBankCards($userData['id'],10);

        $inline_keybaord = [];
        if (!empty($BankCardList)){
            $inline_keyboard[] = [
                ['text' => 'جزییات', 'callback_data'=>'bankcard_status'],
                ['text' => 'وضعیت', 'callback_data'=>'bankcard_status'],
                ['text' => 'نام بانک', 'callback_data'=>'bankcard_amount'],
                ['text' => 'شناسه', 'callback_data'=>'bankcard_title'],
            ];
        }
        foreach($BankCardList as $bankkcard) {
            $bankkcardId = $bankkcard['id'];
            $bankcardname = getBankName($bankkcard['bank'] ?? "UNKNOWN");
            $bankcardStatus = App\Enum\BankCardStatus::from($bankkcard['status'])->text();
            $inline_keyboard[] = [
                ['text' => '🔎', 'callback_data' => 'bankcard_data_'.$bankkcardId],
                ['text' => $bankcardStatus, 'callback_data' => 'bankcard_data_'.$bankkcardId],
                ['text' => $bankcardname, 'callback_data' => 'bankcard_data_'.$bankkcardId],
                ['text' => $bankkcardId, 'callback_data' => 'bankcard_data_'.$bankkcardId],
            ];
        }
        $inline_keyboard[] = [
            ['text' => '➕ افزودن کارت بانکی', 'callback_data'=>'add_bank_card'],
            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
        ];

        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "در این بخش شما لیست کارت های بانکی خود را مشاهده می‌کنید و می‌توانید آنها را مدیریت کنید.",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($data == "add_bank_card") {
        setUserStep($update->cb_data_chatid,'none');
        $userData = getUser($update->cb_data_chatid);
        $group_id = App\Enum\UserGroupEnum::from($userData['group_id'])->bankCardLimit();
        $getUserBankCardsPending = count(getUserBankCardsPending($userData['id']));
        if($getUserBankCardsPending >= 0) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "یک کارت درحالت بررسی وجود دارد",
                'show_alert' => true,
            ]);
            return;
        }
        $getCountBankCardActive = count(getUserBankCardsActive($userData['id']));

        if($getCountBankCardActive >= $group_id) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "❌ در گروه کاربری شما ، امکان ثبت کارت بیشتر نمی باشد.",
                'show_alert' => true,
            ]);
            return;
        } else {
            setUserStep($update->cb_data_chatid,'addBankCard');
            setUserTmp($update->cb_data_chatid,'user_id',$userData['id']);
            Telegram::api('editMessageText',[
                'chat_id' => $update->cb_data_chatid,
                "message_id" => $update->cb_data_message_id,
                'text' => "🔹 چرا باید در یک ربات VPN احراز هویت انجام دهیم؟ 🤖🔑

برای جلوگیری از فیشینگ و حفاظت از اطلاعات شما، نیاز است که عکس کارت بانکی خود را ارسال کنید. ✅

▫️ فیشینگ به معنای برداشت و انتقال غیرمجاز وجه از کارت بانکی بدون اطلاع صاحب آن است. ⚠️

پس از تایید کارت بانکی شما، عکس کارت بانکی به سرعت از سرورهای ما حذف خواهد شد. 🗑

لطفاً شماره کارت خود را بدون خط تیره و فاصله وارد کنید. 

به عنوان مثال: 1234567890123456 ✨",
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'bankCards'],
                        ]
                    ],
                ]
            ]);
        }
    } elseif ($data == "AddBalance") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'👝 کیف پول','text');
        $userData = getUser($update->cb_data_chatid);
        $cardBanks = getCardsBank($userData['id']);
        $group_id = $userData['group_id'];
        $addBalance = "AddBalance";
        if ($group_id < 1 or count($cardBanks) < 1) {
            Telegram::api('editMessageText', [
                'chat_id' => $update->cb_data_chatid,
                'message_id' => $update->cb_data_message_id,
                'text' => "برای افزایش اعتبار ، لازم هست به منوی کارت بانکی مراجعه کرده و کارت بانکی خود را ثبت کنید!",
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'wallet'],
                        ]
                    ],
                ]
            ]);
            return; 
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
    } elseif ($data == "support") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'/start','text');

        Telegram::api('deleteMessage',[
            'message_id' => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid
        ]);

        Telegram::api('sendMessage',[
            'chat_id' => $update->cb_data_chatid ?? $chat_id,
            'text' => "خوش آمدید به بخش پشتیبانی! 👋 

📩 برای مشکلات و سوالات خود، تیکت ارسال کنید.

❓ سوالات رایج را بررسی کنید تا سریع‌تر به پاسخ‌ها برسید.

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'تیکت 📬', 'callback_data'=>'Tickets'],
                        ['text' => 'سوالات رایج ❓', 'callback_data'=>'faqs'],
                    ],
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ],
                ],
            ]
        ]);
    } elseif ($data == "faqs") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'faqs','data');
        Telegram::api('editMessageText', [
                'chat_id' => $update->cb_data_chatid,
                'message_id' => $update->cb_data_message_id,
                'text' => "سوالات خود را از لیست زیر انتخاب کنید یا سوال جدیدی بپرسید !",
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'سابسکریپشن v2ray چیست؟', 'callback_data'=>'faq_1'],
                        ],
                        [
                            ['text' => 'سرویس های من چند کاربره است؟', 'callback_data'=>'faq_2'],
                        ],
                        [
                            ['text' => 'چرا در آپدیت تعداد لینک ها، کم و زیاد میشود؟', 'callback_data'=>'faq_3'],
                        ],
                        [
                            ['text' => 'چرا سرویس ها محدودیت زمانی دارند ؟', 'callback_data'=>'faq_4'],
                        ],
                        [
                            ['text' => 'مدت زمان اشتراک من چگونه محاسبه می شود ؟', 'callback_data'=>'faq_5'],
                        ],
                        [
                            ['text' => 'امکان لغو کردن سرویس و عودت وجه وجود دارد ؟', 'callback_data'=>'faq_6'],
                        ],
                        [
                            ['text' => 'آیا وی‌پی‌ان برای تماشای فیلم‌های آنلاین مناسب است؟', 'callback_data'=>'faq_7'],
                        ],
                        [
                            ['text' => 'سوال جدید بپرس!', 'callback_data'=>'new_ticket'],
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'support'],
                        ]
                    ],
                ]
            ]);
    } elseif ($data == 'new_ticket') {
        setUserStep($update->cb_data_chatid,'none');
        $userData = getUser($update->cb_data_chatid);
        $TicketList = getUserTickets($userData['id']);
        $lastTicketTime = strtotime($TicketList[0]['created_at']);
        if((time() - $lastTicketTime) < 60) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "در هر دقیقه تنها مجاز به ثبت یک تیکت می باشید. ⛔️",
                'show_alert' => true,
            ]);
            return;
        } else {
            setUserStep($update->cb_data_chatid,'new_ticket_1');
            Telegram::api('forwardMessage', [
                'chat_id' => $update->cb_data_chatid,
                'from_chat_id' => '@YozNet',
                'message_id' => 30,  
            ]);
            Telegram::api('sendMessage',[
                'chat_id' => $update->cb_data_chatid,
                'text' => "ممنون که مشکل خود را با ما به اشتراک گذاشتید! 😊 لطفاً برای ایجاد یک تیکت جدید، یک موضوع مرتبط با مشکل‌تان را ارسال فرمایید. 🙏✨",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'support'],
                        ],
                    ],
                ]
            ]);
        }
        //
        
    } elseif ($data == "Tickets") {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'Tickets','data');
        $userData = getUser($update->cb_data_chatid);
        $TicketList = getUserTickets($userData['id']);
        setUserTmp($update->cb_data_chatid,'show_ticket',0);
        $inline_keybaord = [];
        $inline_keyboard[] = [
            ['text' => 'جزییات', 'callback_data'=>'ticket_details'],
            ['text' => 'وضعیت', 'callback_data'=>'ticket_status'],
            ['text' => 'دپارتمان', 'callback_data'=>'ticket_department'],
            ['text' => 'موضوع', 'callback_data'=>'ticket_title'],
            ['text' => 'شناسه', 'callback_data'=>'ticket_id'],
        ];
        foreach($TicketList as $ticket) {
            $ticketId = $ticket['id'];
            $status = App\Enum\TicketStatus::from($ticket['status'])->text();
            $inline_keyboard[] = [
                ['text' => '🔎', 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
                ['text' => $status, 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
                ['text' => GetDepartments($ticket['department']), 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
                ['text' => $ticket['title'], 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
                ['text' => $ticketId, 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
            ];
        }
        $inline_keyboard[] = [
            ['text' => 'سوال جدید بپرس!', 'callback_data'=>'new_ticket'],
            ['text' => 'بازگشت ◀️', 'callback_data'=>'support'],
        ];

        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "در این بخش شما لیست تیکت های خود را مشاهده می‌کنید و می‌توانید آنها را مدیریت کنید.",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($data == 'complate_order_service') {
        $userData = getUser($update->cb_data_chatid);
        $userTmp = getAllUserTmp($update->cb_data_chatid);

        $service_type = $userTmp['service_type'];
        $service_size = $userTmp['service_size'];
        $service_orderby = $userTmp['service_orderby'];

        $price = getServicePrice($update->cb_data_chatid,$service_type);
        $price_irt = $price['irt'] * $service_size;
        $price_yc = $price['yc'] * $service_size;


        if($userData['irr_wallet'] < $price_yc) {
            $diff = displayNumber($price_yc - $userData['irr_wallet'],true);

            $config = GetConfig();
            $diff_toman = $config['yc_price'] * $diff;
            setUserTmp($update->cb_data_chatid,'user_id',$userData['id']);
            setUserStep($update->cb_data_chatid,'addBalance_2');
            setUserTmp($update->cb_data_chatid,'addBalance_amount',$diff_toman);
            setUserTmp($update->cb_data_chatid,'waitpay_for_service',1);


            $userID = getUser($update->cb_data_chatid)['id'];
            $cardBanks = getCardsBank($userID);

            foreach ($cardBanks as $cardData) {
                $inline_keyboard[] = [
                    ['text' => splitCardNumber($cardData['card_number'])." (".getBankName($cardData['bank']).")", 'callback_data'=>'addBalance_select_'. $cardData['id']],
                ];
            }
            #  setUserTmp($update->cb_data_chatid,'service_orderby
            # order_service2_'.$service_orderby.'_'.$service_type.'_'.$service_size
            # order_service2_bygig_'.$serviceType.'_'.$volume
            setBackTo($update->cb_data_chatid,'complate_order_service','data',false,true);
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service2_'.$service_orderby.'_'.$service_type.'_'.$service_size],
            ];
            Telegram::api('editMessageText',[
                "message_id" => $update->cb_data_message_id,
                'chat_id' => $update->cb_data_chatid,
                'parse_mode' => 'Markdown',
                'text' => "متأسفانه، حساب شما اعتبار کافی برای تهیه این سرویس را ندارد. ❌😔

برای ادامه‌ی فرآیند، مبلغ $diff یوزکوین معادل ( ".number_format($diff_toman, 0, '', ',')." تومان ) اعتبار دیگر نیاز دارید.

برای افزایش اعتبار، لطفاً بفرمایید قصد دارید با کدام یک از کارت‌های بانکی خود پرداخت را انجام دهید؟ ✨",
                'reply_markup' => [
                    'inline_keyboard' => $inline_keyboard
                ]
            ]);
            return;
        } 

        $service_id = Database::create('YN_services',
            ['user_id','buy_method','main_traffic','status','created_at', 'updated_at'],
                [
                    $userData['id'],
                    3,
                    $service_size,
                    App\Enum\ServiceStatus::PENDING->value,
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")
                ]
        );
        if ($service_type == "unlimited") {
            $unlimitedPlans = GetAllServices()[$service_type]['plans'];
            foreach ($unlimitedPlans as $planId => $plan) {
                if ($plan['data_total'] == $service_size) {
                    $service_size = $planId;
                    break;
                }
            }
        }
        Telegram::api('deleteMessage',[
            'message_id' => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid
        ]);
        $webservice = API::buyservice(["user_id" => $userData['id'],"service_id" => $service_id,'type' => $service_type,'value' => $service_size]);
        if ($webservice['status'] == true) {
            setBackTo($update->cb_data_chatid,'/start','text');
            Telegram::api('sendMessage',[
                'chat_id' => $update->cb_data_chatid,
                'text' => "سرویس ( $service_id ) با موفقیت تهیه شد. بابت تهیه این سرویس از شما سپاسگزاریم.

لازم به ذکر است که سرویس شما هنوز نهایی نشده و در حال ساخت است. لطفاً منتظر بمانید تا فرایند فعال‌سازی به طور کامل انجام شود. به محض اتمام، به شما اطلاع‌رسانی خواهد شد.",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                        ]
                    ],
                ]
            ]);
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $update->cb_data_chatid,
                'text' => "سرویس شما به دلیل ( ".json_decode($webservice['message'])." ) ساخته نشد.",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'Tickets'],
                        ]
                    ],
                ]
            ]);
        }
    } elseif ($data!= '' && preg_match("/ticket_data_(.*)_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
        $ticketId = $result[1];
        $ticketMessageId = $result[2];
        $show_ticket = getUserTmp($update->cb_data_chatid,'show_ticket');
        $getTicketMessage = getTicketMessage($ticketId);
        if($show_ticket == 0)  {
            $ticketData = getTicketData ($ticketId);
            setUserTmp($update->cb_data_chatid,'show_ticket',1);
            $ticketKeyboard = [];
            if (in_array($ticketData['status'],[1,2,3])) {
                $lastMessage = $getTicketMessage[0];
                $lastMessageTime = strtotime($lastMessage['created_at']);
                $currentTime = time();

                if (($currentTime - $lastMessageTime) >= 60) {
                    $ticketKeyboard[] = [
                        ['text' => '🔸 ثبت پاسخ جدید', 'callback_data' => 'ticket_reply_to_' . $ticketId],
                        ['text' => 'بازگشت ◀️', 'callback_data' => 'Tickets'],
                    ];
                } else {
                    $timeRemaining = 60 - ($currentTime - $lastMessageTime);
                    $ticketKeyboard[] = [
                        ['text' => "⏳ امکان پاسخ جدید در $timeRemaining ثانیه", 'callback_data' => 'limitreply'],
                    ];
                    $ticketKeyboard[] = [
                        ['text' => 'بازگشت ◀️', 'callback_data' => 'Tickets'],
                    ];
                }
            } else {
                $ticketKeyboard[] = [
                    ['text' => 'بازگشت ◀️', 'callback_data'=>'Tickets'],
                ];
            }
            Telegram::api('sendMessage',[
                'chat_id' => $update->cb_data_chatid,
                'text' => "🛠 جزئیات تیکت 🛠 
🆔 شناسه : ".$ticketData['id']."
✨ عنوان: ".$ticketData['title']."
🔧 دپارتمان : ".GetDepartments($ticketData['department'])."
🔍 وضعیت : ".App\Enum\TicketStatus::from($ticketData['status'])->text()."
📅 تاریخ ایجاد : ".$ticketData['created_at']."
🗓 آخرین بروزرسانی : ".$ticketData['updated_at']."

                برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
                'reply_markup' => [
                    'inline_keyboard' => $ticketKeyboard,
                ]
            ]);
        }
        $inline_keyboard = [];
        if(!is_null($getTicketMessage[$ticketMessageId]['file_id'])) {
            $inline_keyboard[] = [
                ['text' => '◾️ دانلود پیوست', 'callback_data'=>'ticket_attachment_'.$ticketId.'_'.$ticketMessageId],
            ];
        }
        if(isset($getTicketMessage[$ticketMessageId + 1]) && isset($getTicketMessage[$ticketMessageId - 1])) {
            $inline_keyboard[] = [
                ['text' => 'بعدی ⬅️', 'callback_data'=>'ticket_data_'.$ticketId.'_'.$ticketMessageId + 1],
                ['text' => 'قبلی ➡️', 'callback_data'=>'ticket_data_'.$ticketId.'_'.$ticketMessageId - 1],
            ];
        } elseif (isset($getTicketMessage[$ticketMessageId + 1]) && !isset($getTicketMessage[$ticketMessageId - 1])) {
            $inline_keyboard[] = [
                ['text' => 'بعدی ⬅️', 'callback_data'=>'ticket_data_'.$ticketId.'_'.$ticketMessageId + 1],
            ];
        } elseif (!isset($getTicketMessage[$ticketMessageId + 1]) && isset($getTicketMessage[$ticketMessageId - 1])) {
            $inline_keyboard[] = [
                ['text' => 'قبلی ➡️', 'callback_data'=>'ticket_data_'.$ticketId.'_'.$ticketMessageId - 1],
            ];
        }
        $message = $getTicketMessage[$ticketMessageId];
        $strip_message = strip_tags($message['message']);
        $response = "";
        if ($message['is_admin']) {
            $response = "🌟 پیام از طرف پشتیبان به شناسه ( ".$message['admin_id']." ) :
            📅 جزئیات پیام:
            ".$strip_message."
            - زمان ارسال: ". $message['created_at'];
        } elseif ($message['is_system']) {
            $response = "🚨 پیام سیستم :
            🔔 جزئیات :
            ".$strip_message."
            - زمان ارسال: ". $message['created_at'];
        } else {
            $response = "💬 پیام از کاربر :
            تیکت به شماره $ticketId از کاربر با شناسه ".$message['user_id']." ثبت شده است.
            🔔 جزئیات :
            ".$strip_message."
            - زمان ارسال: ". $message['created_at'];
        }
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => $response,
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($data!= '' && preg_match("/ticket_reply_to_(.*)/",$data,$result)) {
        $ticketId = $result[1];
        setUserStep($update->cb_data_chatid,'reply_to_ticket');
        $userData = getUser($update->cb_data_chatid);
        setUserTmp($update->cb_data_chatid,'user_id',$userData['id']);
        setUserTmp($update->cb_data_chatid,'reply_ticket_id',$ticketId);
        setUserTmp($update->cb_data_chatid,'show_ticket',0);
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => 'می‌توانید به دو شکل پاسخ خود را ارسال کنید: 
1️⃣ ارسال یک عکس به همراه توضیحات  📸✍️
2️⃣ ارسال توضیحات خالی 📝

لطفاً یکی از این دو حالت را برای ما ارسال فرمایید یا بر روی دکمه بازگشت ◀️ کلیک نمایید.',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data' => 'ticket_data_'.$ticketId.'_0'],
                    ]
                ],
            ]
        ]);
    } elseif ($data!= '' && preg_match("/ticket_attachment_(.*)_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
        $getTicketMessages = getTicketMessage($result[1]);
        $getTicketMessage = $getTicketMessages[$result[2]];
        Telegram::api('sendPhoto',[
            'chat_id' => $update->cb_data_chatid,
            'photo' => $getTicketMessage['file_id'],
            'caption' => 'پیوست برای تیکت: '.$result[1]
        ]);

    } elseif ($data!= '' && preg_match("/set_default_card_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
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
    } elseif ($data!= '' && preg_match("/bankcard_data_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
        $BankCard = getbankcard($result[1]);
        if ($BankCard['status'] == App\Enum\BankCardStatus::PENDING->value || $BankCard['status'] == App\Enum\BankCardStatus::WAITING_CONFIRMATION->value) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "⚠️ تا تایید شدن کارت بانکی خود لطفا منتظر بنمایید.",
                'show_alert' => true,
            ]);
            return;
        }
        $bankcardname = getBankName($BankCard['bank'] ?? "UNKNOWN");
        $cardnumber = splitCardNumber($BankCard['card_number']);
        $bankcardStatus = App\Enum\BankCardStatus::from($BankCard['status'])->text();

        $bankcardReason = $BankCard['reason_id'];
        $bankcardReasonText = "";
        if (($bankcardReason != null && $BankCard['status'] == 2) ) {
            $db = Database::select("YN_bank_card_reasons", ["*"], "id =?", [$bankcardReason])[0];
            $bankcardReasonText = "🔴 دلیل رد: ".$db['content'];
        }

        $bankcardDate = date('Y-m-d H:i:s', strtotime($BankCard['created_at']));
        $inline_keybaord = [];
        if ($BankCard['status'] == App\Enum\BankCardStatus::APPROVED->value){
            $inline_keyboard[] = [
                ['text' => 'حذف 🗑', 'callback_data'=>'delete_bankcard_'.$BankCard['id']],
                ['text' => 'بازگشت ◀️', 'callback_data'=>'bankCards'],
            ];
        } else {
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'bankCards'],
            ];
        }
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "📊 جزئیات کارت بانکی

🏦 نام بانک: $bankcardname
💳 شماره کارت: $cardnumber
✅ وضعیت کارت: $bankcardStatus 
$bankcardReasonText

📅 تاریخ ایجاد: $bankcardDate

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);

    } elseif ($data!= '' && preg_match("/delete_bankcard_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
        $BankCard = getbankcard($result[1]);
        $BankcardactiveCount =  count(getUserBankCardsActive($BankCard['user_id']));
        if ($BankCard['status'] != App\Enum\BankCardStatus::APPROVED->value) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "❌ کارت مورد نظر فعال نیست و امکان حذف آن وجود ندارد.",
                'show_alert' => true,
            ]);
            return;
        }
        if ($BankcardactiveCount <= 1) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "❌ امکان حذف کارت وجود ندارد، زیرا حداقل یک کارت فعال باید وجود داشته باشد.",
                'show_alert' => true,
            ]);
            return;
        }
        Database::update('YN_bank_cards',['status'],[3],'id = ?',[$BankCard['id']]);
        Telegram::api('editMessageText', [
            'chat_id' => $update->cb_data_chatid,
            'message_id' => $update->cb_data_message_id,
            'text' => "کارت بانکی شما با موفقیت حذف شد ✅
برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎 ",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'bankCards'],
                    ]
                ],
            ]
        ]);

    } elseif ($data!= '' && preg_match("/invoice_data_(.*)/",$data,$result)) {
        setUserStep($update->cb_data_chatid,'none');
        setBackTo($update->cb_data_chatid,'Invoices','data');

        $invoices = getInvoice($result[1]);
        if ($invoices['status'] == 0) {
            Telegram::api('answerCallbackQuery', [
                'callback_query_id' => $update->cb_data_id,
                'text' => "لطفا از طریق سایت اقدام به پرداخت نمایید . ⛔️",
                'show_alert' => true,
            ]);
            return;
        }
        $invoiceYcAmount = formatWallet($invoices['yc_amount']);
        $invoiceStatus = App\Enum\InvoiceStatus::from($invoices['status'])->text();
        $invoiceAmount = number_format($invoices['amount'], 0, '', ',');
        $invoiceTaxAvoidance = number_format($invoices['tax_avoidance'], 0, '', ',');

        $invoiceReason = $invoices['reason_id'];
        $invoiceReasonText = "";
        if (($invoiceReason != null && $invoices['status'] == 3) ) {
            $db = Database::select("YN_invoices_reasons", ["*"], "id =?", [$invoiceReason])[0];
            $invoiceReasonText = "🔴 دلیل رد: ".$db['content'];
        }

        $invoiceDate = date('Y-m-d H:i:s', strtotime($invoices['created_at']));
        $invoicePaidAt = date('Y-m-d H:i:s', strtotime($invoices['paid_at']));

        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "📊 جزئیات فاکتور

💰 مبلغ : $invoiceAmount ( تومان )
🪙 مبلغ : $invoiceYcAmount ( یوز کوین )
🚫 مانع زنی مالیاتی: $invoiceTaxAvoidance ت ( مانع زنی مالیاتی برای اینکه ما تراکنش‌های تکراری روی یک حساب بانکی نداشته باشیم اینه که از این روش برای جلوگیری از مشکلات مالیاتی استفاده می‌کنیم. همچنین وقتی این رقم به فاکتور اضافه میشه، با مبلغ نهایی جمع میشه و بعد از تایید رسید به حساب شما واریز میشه )
✅ وضعیت: $invoiceStatus 
$invoiceReasonText

📅 تاریخ ایجاد: $invoiceDate
💳 تاریخ پرداخت: $invoicePaidAt

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);

    } elseif ($data!= '' && preg_match("/faq_(.*)/",$data,$result)) {
        setBackTo($update->cb_data_chatid,'faqs','data');
        $response = "";
        switch($result[1]) {
            case 1:
                $response = "سابسکریپشن v2ray چیست؟
 🔹 سابسکرایپ v2ray یک سرویس است که به شما امکان می‌دهد به صورت اتوماتیک لینک‌های خود را بروز کنید. اگر لینک شما برای دسترسی به اینترنت مسدود شود یا نیاز به تغییر داشته باشد، سابسکرایپ v2ray به طور خودکار لینک جدیدی را برای شما تهیه و از طریق نرم‌افزار مورد استفاده‌تان اعمال می‌کند. به این ترتیب شما نیازی ندارید که به صورت دستی لینک جدید را از یک وب‌سایت دریافت کنید و آن را به نرم‌افزار اضافه کنید. سابسکرایپ v2ray به شما این امکان را می‌دهد که به راحتی و بدون درگیری در جزئیات فنی، از اینترنت با لینک‌های بروز و کارآمد استفاده کنید.";
                break;
            case 2 :
                $response = "سرویس های من چند کاربره است؟
🔸 سرویس های ما محدودیت کاربر ندارد و شما میتوانید تا بی نهایت کاربر به لینک اتون متصل کنید.";
                break;
            case 3 :
                $response = "چرا در آپدیت تعداد لینک ها، کم و زیاد میشود؟
🔹 در سرویس سابسکریپشن v2ray ، برخی از متود ها موجب فیلتر شدن سرور می شود و ما مجبور هستیم بصورت دوره ای ، چندین متود جهت اتصال شما به اینترنت فعال بکنیم";
                break;
            case 4 :
                $response = "چرا سرویس ها محدودیت زمانی دارند ؟
🔸 ما به دلیل نوسانات شدید ارز، تصمیم گرفته‌ایم سرویس خدمات ماهانه را به شما ارائه دهیم. این تصمیم به منظور ایجاد پایداری در ارائه خدمات به شما اتخاذ شده است. با این روش، شما به عنوان مشتریان عزیز می‌توانید به راحتی با نوسانات ارز مقابله کرده و همچنین از سرویس‌های ما با کیفیت بالا بهره‌مند شوید.";
                break;
            case 5 :
                $response = "مدت زمان اشتراک من چگونه محاسبه می شود ؟
🔹 با خرید اکانت ما، شما بلافاصله به محتوا و خدمات ما دسترسی پیدا می‌کنید. اکانت شما فوراً فعال می‌شود و شما می‌توانید به تمامی ویژگی‌ها و محتواهای ما در طی مدت 30 روز دسترسی داشته باشید. این مدت زمان به شما اجازه می‌دهد تا به طور کامل از خدمات و محتواهای ارائه شده توسط اکانت ما بهره‌برداری کنید و آنها را تجربه نمایید.";
                break;
            case 6 :
                $response = "امکان لغو کردن سرویس و عودت وجه وجود دارد ؟
🔹 اگر از تهیه سرویس کمتر از 48 ساعت گذشته و حجم مصرفی شما صفر باشد، امکان بازگشت وجه به کیف پول شما وجود دارد.";
                break;
            case 7 :
                $response = "آیا وی‌پی‌ان برای تماشای فیلم‌های آنلاین مناسب است؟
🔸 ممکن است کمی سرعت کاهش یابد، اما وی‌پی‌ان‌های ما باکیفیت هستند، معمولاً این تاثیر را به حداقل می‌رسانند.. 🎬🌍";
                break;
            default:
                $response = "لطفا با برگشت به منوی قبل و کلیک بر روی سوالا جدید بپرس ! ، سوال خود را مطرح کنید.";
        }
        Telegram::api('editMessageText',[
            'chat_id' => $update->cb_data_chatid,
            "message_id" => $update->cb_data_message_id,
            'text' => "$response

برای ادامه، روی یکی از دکمه‌های زیر کلیک کنید! 👇😎",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                    ]
                ],
            ]
        ]);

    } elseif ($data!= '' && preg_match("/order_service_(.*)/",$data,$result)) {
        $serviceType = $result[1];
        
        setBackTo($update->cb_data_chatid,'⚜️ ثبت سرویس جدید','text');
        setUserStep($update->cb_data_chatid,'order_service');
        setUserTmp($update->cb_data_chatid,'order_service_type',$serviceType);
        $services = GetAllServices()[$serviceType];
        $serviceData = $services['plans'] ?? null;
        $price = $servicePrice = getServicePrice($update->cb_data_chatid,$serviceType);
        
        $inline_keyboard = [];
        
        if(!is_null($serviceData)) {
            foreach($serviceData as $planId => $plan) {
                $p = number_format($price['irt'] * $plan['data_total'], 0, '', ',');
                $inline_keyboard[] = [
                    ['text' => $plan['name'] . " ( $p ت ) " , 'callback_data'=> 'order_service2_plan_'.$serviceType.'_'. $planId]
                ];
            }
            $inline_keyboard[] = [
                ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
            ];
        } else {
            $inline_keyboard[] = [
                ['text' => '-', 'callback_data' => 'header_select'],
                ['text' => 'قیمت', 'callback_data' => 'header_price'],
                ['text' => 'نوع', 'callback_data' => 'header_price'],
                ['text' => 'حجم', 'callback_data' => 'header_volume'],
            ];
            $baseVolumes = [10, 20, 50, 100];
            foreach ($baseVolumes as $volume) {
                $totalPrice = $volume * $price['irt'];
                $inline_keyboard[] = [
                    ['text' => 'انتخاب', 'callback_data'=> 'order_service2_bygig_'.$serviceType.'_'.$volume],
                    ['text' => number_format($totalPrice, 0, '', ',') . ' ت', 'callback_data'=> 'order_service2_bygig_'.$serviceType.'_'.$volume],
                    ['text' => $services['name'], 'callback_data'=> 'order_service2_bygig_'.$serviceType.'_'.$volume],
                    ['text' => $volume . ' GB', 'callback_data'=> 'order_service2_bygig_'.$serviceType.'_'.$volume],
                ];
            }
            $inline_keyboard[] = [
                ['text' => 'حجم دلخواه', 'callback_data'=>'order_service2_bygig_'.$serviceType.'_custom'],
                ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
            ];
        }
        
        Telegram::api('editMessageText',[
            "message_id" => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid,
            'text' => "شما چقدر در ماه مصرف ( اینترنت بدون اختلال ) دارید ؟📊 🤔

به همون مقدار حجم سفارش بدید!

اگر حجم مورد نظر رو پیدا نکردید، روی گزینه ( حجم دلخواه ) کلیک کنید. ✨",
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard
            ]
        ]);
    } elseif ($data!= '' && preg_match("/order_service2_(.*)_(.*)_(.*)/",$data,$result)) {
        $order_service_by = $result[1]; 
        $service_type = $result[2];
        $userData = getUser($update->cb_data_chatid);
        setBackTo($update->cb_data_chatid,'order_service_'.$service_type,'data');
        setUserStep($update->cb_data_chatid,'none');
        setUserTmp($update->cb_data_chatid,'service_orderby',$order_service_by);
        setUserTmp($update->cb_data_chatid,'service_type',$service_type);

        $serviceData = GetAllServices()[$service_type];

        $t = "";
        if($order_service_by == "bygig") {
            $size = $result[3];
            if($userData['group_id'] == 0 && $size > 10) {
                Telegram::api('editMessageText',[
                    "message_id" => $update->cb_data_message_id,
                    'chat_id' => $update->cb_data_chatid,
                    'parse_mode' => 'Markdown',
                    'text' => "در لول فعلی شما، تنها مجاز به ثبت حداکثر 10 گیگ حجم هستید! 
    
    برای خرید حجم بیشتر، لطفاً با مراجعه به بخش کیف پول و احراز هویت با کارت بانکی، به لول بعدی ارتقا یابید! 🔝💳✨",
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [
                                    ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service_'.$service_type],
                                ]
                            ],
                        ]
                ]);
                return ;
            } 
            $t = "$size گیگ حجم";
            if($size == "custom") {
                setUserStep($update->cb_data_chatid,'custom_value');

                $limit = App\Enum\UserGroupEnum::from($userData['group_id'])->trafficLimit();

                setUserTmp($update->cb_data_chatid,'service_limit',$limit);
                Telegram::api('editMessageText',[
                    "message_id" => $update->cb_data_message_id,
                    'chat_id' => $update->cb_data_chatid,
                    'parse_mode' => 'Markdown',
                    'text' => "لطفاً حجم مورد نیاز خود را از بین 5 گیگابایت تا $limit گیگابایت وارد کنید! ✨",
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service_'.$service_type],
                            ]
                        ],
                    ]
                ]);
                return;
            }
        } elseif($order_service_by == "plan") {
            $plan_id = $result[3];
            if($userData['group_id'] == 0 && $plan_id != 1) {
                Telegram::api('editMessageText',[
                    "message_id" => $update->cb_data_message_id,
                    'chat_id' => $update->cb_data_chatid,
                    'parse_mode' => 'Markdown',
                    'text' => "در لول فعلی شما، تنها مجاز به ثبت پلن مصرف منصفانه روزانه 10 گیگ هستید! 
    
    برای خرید پلن بیشتر، لطفاً با مراجعه به بخش کیف پول و احراز هویت با کارت بانکی، به لول بعدی ارتقا یابید! 🔝💳✨",
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [
                                    ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service_'.$service_type],
                                ]
                            ],
                        ]
                ]);
                return;
            }
            $size = $serviceData['plans'][$plan_id]['data_total'];
            $t = "پلن ".$serviceData['plans'][$plan_id]['name'];
        }
        
        $price = getServicePrice($update->cb_data_chatid,$service_type);

        $price_irt = $price['irt'] * $size;
        $price_yc = $price['yc'] * $size;

        setUserTmp($update->cb_data_chatid,'service_size',$size);
        Telegram::api('editMessageText',[
            "message_id" => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid,
            'parse_mode' => 'Markdown',
            'text' => "🔔 شما در حال خرید **$t** از سرویس ". $serviceData['name'] ." هستید.

💰 هزینه این سرویس: $price_yc یوزکوین معادل ".number_format($price_irt, 0, '', ',')." تومان می شود. 

✅ در صورت تایید، بر روی ادامه کلیک کنید و چنانچه مورد تایید نیست، بر روی بازگشت کلیک کنید.",
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'ادامه خرید 🎗', 'callback_data'=>'complate_order_service'],
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service_'.$service_type],
                    ]
                ]
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

    if ($text != '' && $step == 'set_ip_address_1') {
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
            'reply_to_message_id' => $update->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'web_service'],
                    ]
                ],
            ]
        ]);
    } elseif ($text != '' && $step == 'addBalance_1') {
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
    } elseif ($step == 'addBalance_2' && $data!= '' && preg_match("/addBalance_select_(.*)/",$data,$result)) {
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
                $iban = $cardNumber['iban'] ?? 'تنظیم نشده';
                $bank = getBankName($cardNumber['bank']);
                $firstName = $cardNumber['first_name'] ?? 'تنظیم نشده';
                $lastName = $cardNumber['last_name'] ?? 'تنظیم نشده';
                $fullname =  $firstName." ".$lastName;
                
            } else {
                $findAsName = getBankByName($data['bank']);
                if(count($findAsName) > 0) {
                    $randKey = array_rand($findAsName);
                    $cardBankNumber = $findAsName[$randKey]['card_number'];
                    $cardBankImage =  $findAsName[$randKey]['card_image_file_id'];
                    $cardBankId =  $findAsName[$randKey]['id'];
                    $iban = $findAsName[$randKey]['iban'];
                    $bank = getBankName($findAsName[$randKey]['bank']);
                    $firstName = $findAsName[$randKey]['first_name'] ?? 'تنظیم نشده';
                    $lastName = $findAsName[$randKey]['last_name'] ?? 'تنظیم نشده';
                    $fullname =  $firstName." ".$lastName;
                    # $fullname = $findAsName[$randKey]['first_name'] ?? 'تنظیم نشده' . " " . $findAsName[$randKey]['last_name'] ?? 'تنظیم نشده';
                } else {
                    $adminCards = getAdminCards();
                    $randKey = array_rand($adminCards);
                    $cardBankNumber = $adminCards[$randKey]['card_number'];
                    $cardBankImage =  $adminCards[$randKey]['card_image_file_id'];
                    $cardBankId =  $adminCards[$randKey]['id'];
                    $iban = $adminCards[$randKey]['iban'];
                    $bank = getBankName($adminCards[$randKey]['bank']);
                    $firstName = $adminCards[$randKey]['first_name'] ?? 'تنظیم نشده';
                    $lastName = $adminCards[$randKey]['last_name'] ?? 'تنظیم نشده';
                    $fullname =  $firstName." ".$lastName;
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
            Telegram::api('deleteMessage',[
                'message_id' => $update->cb_data_message_id,
                'chat_id' => $update->cb_data_chatid
            ]);

            $backData = getBack($update->cb_data_chatid);
            if($backData['to'] != 'complate_order_service') {
                setBackTo($update->cb_data_chatid,'wallet','data');
            }
            $sendPhoto = Telegram::api('sendPhoto',[
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
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                        ]
                    ],
                ]
            ]);

            $messageId = json_decode($sendPhoto->getContents(),1)['result']['message_id'];
            setUserTmp($update->cb_data_chatid,'addBalance_message_id',$messageId);
            setUserTmp($update->cb_data_chatid,'addBalance_created_at',time());
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
                deleteUserTmp($chat_id,[
                    'addBalance_cardBankNumber','addBalance_cardBankId','addBalance_userCardId',
                    'addBalance_amount','Tax_value','YC_value',
                    'addBalance_message_id','addBalance_created_at'
                ]);
                $backData = getBack($chat_id);
                if($backData['to'] != 'complate_order_service') {
                    setBackTo($chat_id,'/start');
                }
                Telegram::api('sendMessage',[
                    'chat_id' => $chat_id,
                    'text' => "پرداخت شما با موفقیت به واحد مالی ارسال شد ، بعد از بررسی نتیجه را به شما اطلاع می‌دهیم.
        با تشکر از شما",
                    'parse_mode' => 'Markdown',
                    'reply_to_message_id' => $update->message_id,
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => 'بازگشت ◀️', 'callback_data'=>'back'],
                            ]
                        ],
                    ]
                ]);
            } 
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "لطفا یک عکس برای ما ارسال کنید.",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'add_bank_card'],
                        ]
                    ],
                ]
            ]);
            return;
        }
    } elseif ($text != '' && $step == "addBankCard") {
        if(!is_numeric($text) or strlen($text) < 16) {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "لطفا با استفاده از اعداد انگلیسی و حداکثر 16 رقم ، شماره کارت خود را مجدد برای ما ارسال کنید.",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'add_bank_card'],
                        ]
                    ],
                ]
            ]);
            return;
        }
        $checkExists = checkUserCardBankExists($text);
        
        if ($checkExists == []) {
            setUserStep($chat_id,'addBankCard_2');
            setUserTmp($chat_id,'add_cardBank_number',$text);
            $response = "با پوشاندن cvv2 و تاریخ انقضا ، عکس کارت خود را برای ما ارسال کنید ! 🥷🏻";
        } else {
            if($checkExists['status'] == 0 or $checkExists['status'] == 1) {
                $response = "🔒✨ متأسفانه امکان افزودن این شماره کارت به سیستم وجود ندارد. لطفاً شماره کارت دیگری را ارسال نمایید. 🙏💳";  
            } else {
                setUserStep($chat_id,'addBankCard_2');
                setUserTmp($chat_id,'add_cardBank_number',$text);
                $response = "با پوشاندن cvv2 و تاریخ انقضا ، عکس کارت خود را برای ما ارسال کنید ! 🥷🏻";
            }
        }
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => $response,
            'reply_to_message_id' => $update->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data'=>'add_bank_card'],
                    ]
                ],
            ]
        ]);
        
    } elseif ($step == "addBankCard_2") {
        if(isset($update->photo_file_id)) {
            $tmp = getAllUserTmp($chat_id);
            $cardnumber = $tmp['add_cardBank_number'];
            $userid = $tmp['user_id'];
            $cardId = Database::create('YN_bank_cards',
            ['user_id','card_number','status','card_image_file_id','created_at', 'updated_at'],
                [
                    $userid,
                    $cardnumber,
                    App\Enum\BankCardStatus::WAITING_CONFIRMATION->value,
                    $update->photo_file_id,
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")]
            );
            $webservice = API::sendCard(["user_id" => $userid,"card_id" => $cardId]);
            if ($webservice['status'] == true) {
                deleteUserTmp($chat_id,['add_cardBank_number']);
                Telegram::api('sendMessage',[
                    'chat_id' => $chat_id,
                    'text' => "کارت شما برای بررسی به واحد فروش ارسال شد.  👥
    
    حداکثر زمان بررسی 2 ساعت کاری می باشد.  🕙 
    
    بعد از تاییدیه، شما میتوانید افزایش اعتبار داشته باشید! ♨️",
                    'parse_mode' => 'Markdown',
                    'reply_to_message_id' => $update->message_id,
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => 'بازگشت ◀️', 'callback_data'=>'bankCards'],
                            ]
                        ],
                    ]
                ]);
            }
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "لطفا یک عکس برای ما ارسال کنید.",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'add_bank_card'],
                        ]
                    ],
                ]
            ]);
        }
    } elseif ($text != '' && $step == 'reply_to_ticket') {
        $tmp = getAllUserTmp($chat_id);
        $ticket_id = $tmp['reply_ticket_id'];
        $user_id =  $tmp['user_id'];
        $attachment = null;
        $reply_text = "";
        setUserTmp($chat_id,'show_ticket',0);
        if(isset($update->photo_file_id)) {
            $attachment = $update->photo_file_id;
            $reply_text = $update->caption;
        } elseif ($text != '') {
            $reply_text = $text;
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "حضرتعالی می‌توانید یکی از دو گزینه زیر را انتخاب نمایید: 

1️⃣ ارسال عکس به همراه توضیحات 📸✍️  
2️⃣ ارسال توضیحات خالی 📝  

لطفاً یکی از این دو حالت را برای ما ارسال فرمایید یا بر روی دکمه بازگشت کلیک نمایید.",
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data' => 'Tickets'],
                        ]
                    ],
                ]
            ]);
            return;
        }
        $ticket_message_id = Database::create('YN_ticket_messages',
            ['user_id','ticket_id','message','file_id','ip_address','created_at', 'updated_at'],
                [
                    $user_id,
                    $ticket_id,
                    $reply_text,
                    $attachment,
                    '127.0.0.1',
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")
                ]
        );
        $webservice = API::sendTicket(["user_id" => $user_id,"ticket_id" => $ticket_id,'type' => 'TicketMessage']);
        if ($webservice['status'] == true) {
            deleteUserTmp($chat_id,['reply_ticket_id']);
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "خبر خوب! تیکت ( $ticket_id ) شما به روز شد.
مشترک گرامی ، پاسخ شما رو دریافت کردیم و به زودی به آن پاسخ می دهیم.",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'ticket_data_'.$ticket_id.'_0'],
                        ]
                    ],
                ]
            ]);
        }
    } elseif ($text != '' && $step == "new_ticket_1") {
        setUserStep($chat_id,'new_ticket_2');
        setUserTmp($chat_id,'new_ticket_title',$text);
        $inline_keyboard = [];
        foreach(GetAllDepartments()['departments'] as $key_name => $key_fa) {
            if ($key_name == "UnableToConnect") {
                continue;
            }
            $inline_keyboard[] = [
                ['text' => $key_fa, 'callback_data' => 'new_ticket_2_'. $key_name]
            ];
        }
        $inline_keyboard[] = [
            ['text' => 'بازگشت ◀️', 'callback_data' => 'new_ticket']
        ];
        Telegram::api('sendMessage',[
            'chat_id' => $chat_id,
            'text' => "تیکت جدید شما با عنوان ( ".$text." ) انتخاب شد! 😍
شما می‌توانید انتخاب کنید که با کدام واحد ارتباط برقرار کنید.  
🔹 این تیکت مربوط به کدام واحد زیر می‌باشد؟
لطفاً واحد مورد نظر خود را انتخاب کنید! 🚀",
            'reply_to_message_id' => $update->message_id,
            'reply_markup' => [
                'inline_keyboard' => $inline_keyboard,
            ]
        ]);
    } elseif ($step == 'new_ticket_2' && $data!= '' && preg_match("/new_ticket_2_(.*)/", $data, $result)) {
        $department = $result[1];
        setUserTmp($update->cb_data_chatid,'new_ticket_department',$department);
        setUserStep($update->cb_data_chatid,'new_ticket_3');
        Telegram::api('deleteMessage',[
            'message_id' => $update->cb_data_message_id,
            'chat_id' => $update->cb_data_chatid
        ]);
        Telegram::api('sendMessage',[
            'chat_id' => $update->cb_data_chatid,
            'text' => "🎉 تبریک! واحد شما برای پیگیری انتخاب شد.
لطفاً مشکل خود را از طریق یکی از روش‌های زیر با ما در میان بگذارید:

1️⃣ ارسال عکس به همراه توضیحات 📸✍️
2️⃣ ارسال توضیحات بدون عکس 📝

در صورت نیاز به بازگشت، دکمه بازگشت را انتخاب نمایید.

منتظر توضیحات شما هستیم تا بتوانیم بهترین راه‌حل را ارائه دهیم! 🌟",
            'reply_to_message_id' => $update->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بازگشت ◀️', 'callback_data' => 'new_ticket'],
                    ]
                ],
            ]
        ]);
    } elseif ($step == 'new_ticket_3') {
        $tmp = getAllUserTmp($chat_id);
        $attachment = null;
        if(isset($update->photo_file_id)) {
            $attachment = $update->photo_file_id;
            $reply_text = $update->caption;
        } elseif ($text != '') {
            $reply_text = $text;
        } else {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "حضرتعالی می‌توانید یکی از دو گزینه زیر را انتخاب نمایید: 

1️⃣ ارسال عکس به همراه توضیحات 📸✍️  
2️⃣ ارسال توضیحات خالی 📝  

لطفاً یکی از این دو حالت را برای ما ارسال فرمایید یا بر روی دکمه بازگشت کلیک نمایید.",
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data' => 'Tickets'],
                        ]
                    ],
                ]
            ]);
            return;
        }
        $userData = getUser($chat_id);
        $ticket_id = Database::create('YN_tickets',
            ['user_id','title','department','status','created_at', 'updated_at'],
                [
                    $userData['id'],
                    $tmp['new_ticket_title'],
                    $tmp['new_ticket_department'],
                    App\Enum\TicketStatus::PENDING->value,
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")
                ]
        );
        $ticket_message_id = Database::create('YN_ticket_messages',
            ['user_id','ticket_id','message','file_id','ip_address','created_at', 'updated_at'],
                [
                    $userData['id'],
                    $ticket_id,
                    $reply_text,
                    $attachment,
                    '127.0.0.1',
                    date("Y-m-d H:i:s"), 
                    date("Y-m-d H:i:s")
                ]
        );
        $webservice = API::sendTicket(["user_id" => $userData['id'],"ticket_id" => $ticket_id,'type' => 'Ticket','message' => $reply_text]);
        if ($webservice['status'] == true) {
            $name = GetDepartments($tmp['new_ticket_department']);
            deleteUserTmp($chat_id,['new_ticket_title','new_ticket_department']);
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "درخواست شما برای بررسی به واحد $name ارسال شد.  👥

حداکثر زمان بررسی 3 ساعت کاری می باشد ( ساعت کاری همه روزه از ساعت 8 صبح الی 12 بامداد ). 🕙

بعد از بررسی ، جواب برای شما ارسال می شود! ♨️",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'Tickets'],
                        ]
                    ],
                ]
            ]);
        }
    } elseif ($step == 'custom_value') {
        $tmp = getAllUserTmp($chat_id);
        $service_limit = $tmp['service_limit'];
        $service_type = $tmp['service_type'];
        if (!is_numeric($text) || $text < 5 || $text > $service_limit) {
            Telegram::api('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ مقدار وارد شده نامعتبر است! لطفاً عددی بین 5 گیگ و $service_limit گیگ وارد کنید.",
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'بازگشت ◀️', 'callback_data'=>'order_service_'.$service_type],
                        ]
                    ],
                ]
            ]);
            return;
        }
        $userData = getUser($chat_id);
        if($userData['group_id'] == 0 && $size > 10) {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "شما اجازه خرید حجم بالای 10 گیگ را ندارید",
            ]);
        } else {

            setUserStep($chat_id,'none');
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "مقدار $text گیگابایت برای خرید انتخاب شد 🎗

    ✅ در صورت تایید، بر روی ادامه کلیک کنید و چنانچه مورد تایید نیست، بر روی بازگشت کلیک کنید.",
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $update->message_id,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => '📯 ادامه ', 'callback_data'=>'order_service2_bygig_'.$service_type.'_'.$text],
                            ['text' => 'بازگشت ◀️ ', 'callback_data'=>'order_service2_bygig_'.$service_type.'_custom'],
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
