<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('error_log', '0.txt');
error_reporting(E_ALL);

include_once("boot.php");

$update = new TelegramUpdates();
try {
    $chat_id = isset($update->chat_id) ? $update->chat_id : null;
    error_log("Chat ID: " . json_encode($chat_id));

    $text = isset($update->text) ? $update->text : null;
    error_log("text: " . json_encode($text));

    if($text == "/start" || (isset($text) && explode(" ", $text)[0] == "/start")) {
        error_log("Command /start received");

        $existing_user = Database::select("YN_users", ["*"], "user_id = ?", [$chat_id]);
        error_log("Existing user check result: " . json_encode($existing_user));

        if ($existing_user) {
            Telegram::api('sendMessage',[
                'chat_id' => $chat_id,
                'text' => "درود! 😅
    به ربات تلگرامی یوزنت خوش آمدید. با استفاده از دکمه‌های زیر می‌توانید با سرویس‌های VPN ما آشنا شوید و به صورت ناشناس در اینترنت گشت و گذار کنید ! 🥷🏻
    کافی است یکی از گزینه‌ها را انتخاب کنید و تجربه جدید خود را آغاز کنید! 👇😎"
            ]);
            error_log("Existing user message sent");
        } else {
            $parts = explode(" ", $update->text);
            $referral_code = isset($parts[1]) ? $parts[1] : null;
            error_log("Referral code extracted: " . json_encode($referral_code));
            if ($referral_code) {
                $referrer = Database::select("YN_users", ["*"], "referral_id = ?", [$referral_code]);
                error_log("Referrer check result: " . json_encode($referrer));
                if ($referrer) {
                    createUser($chat_id);
                    error_log("User created with Chat ID: $chat_id");
                    $referrer_chat_id = $referrer[0]['user_id'];
                    Telegram::api('sendMessage',[
                        'chat_id' => $chat_id,
                        'text' => "شما به پیشنهاد یک دوست قابل‌اعتماد ، به خانواده یوزنت پیوستید!  😍🌷
    از حالا می‌توانید از خدمات حرفه‌ای کاهش پینگ ما لذت ببرید و با خیالی آسوده و ناشناس در اینترنت گشت‌وگذار کنید! 🥷🏻"
                    ]);
                    error_log("Welcome message sent to new user: $chat_id");
                    Telegram::api('sendMessage',[
                        'chat_id' => $referrer_chat_id,
                        'text' => "تشکر ویژه از شما! 👏😊
    با معرفی یوزنت، نشون دادید که همیشه بهترین‌ها رو برای دوستاتون می‌خواید. 😌🌷
    حالا بقیه هم مثل شما می‌تونن لذت یه اینترنت حرفه‌ای و سریع رو تجربه کنند. 🎉
    حضور شما برای ما ارزشمند است. 🌟"
                    ]);
                    error_log("Thank you message sent to referrer: $referrer_chat_id");
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
