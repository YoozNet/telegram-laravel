<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$update = new TelegramUpdates();
try {
    $chat_id = isset($update->chat_id) ? $update->chat_id : null;
    $text = isset($update->text) ? $update->text : null;

    if($text == "/start" || (isset($text) && explode(" ", $text)[0] == "/start")) {
        $existing_user = Database::select("YN_users", ["id"], "user_id = ?", [$chat_id]);

        if ($existing_user) {
            Telegram::api('sendMessage',[
                'reply_to_message_id' => $update->message_id,
                'chat_id' => $chat_id,
                'text' => "درود! 😅
    به ربات تلگرامی یوزنت خوش آمدید. با استفاده از دکمه‌های زیر می‌توانید با سرویس‌های VPN ما آشنا شوید و به صورت ناشناس در اینترنت گشت و گذار کنید ! 🥷🏻
    کافی است یکی از گزینه‌ها را انتخاب کنید و تجربه جدید خود را آغاز کنید! 👇😎"
            ]);
        } else {
            $parts = explode(" ", $update->text);
            $referral_code = isset($parts[1]) ? $parts[1] : null;
            if ($referral_code) {
                $referrer = Database::select("YN_users", ["*"], "referral_id = ?", [$referral_code]);
                if ($referrer) {
                    createUser($chat_id);
                    $referrer_chat_id = $referrer[0]['user_id'];
                    Database::update('YN_users',['referred_by'],[$referral_code],'user_id = ?',[$chat_id]);
                    
                    Telegram::api('sendMessage',[
                        'reply_to_message_id' => $update->message_id,
                        'chat_id' => $chat_id,
                        'text' => "شما به پیشنهاد یک دوست قابل‌اعتماد ، به خانواده یوزنت پیوستید!  😍🌷
    از حالا می‌توانید از خدمات حرفه‌ای کاهش پینگ ما لذت ببرید و با خیالی آسوده و ناشناس در اینترنت گشت‌وگذار کنید! 🥷🏻"
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
                    'chat_id' => $update->chat_id,
                    'text' => "درود! 😅
        به ربات تلگرامی یوزنت خوش آمدید. با استفاده از دکمه‌های زیر می‌توانید با سرویس‌های VPN ما آشنا شوید و به صورت ناشناس در اینترنت گشت و گذار کنید ! 🥷🏻
        کافی است یکی از گزینه‌ها را انتخاب کنید و تجربه جدید خود را آغاز کنید! 👇😎"
                ]);
            }
        }
    }
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
}
