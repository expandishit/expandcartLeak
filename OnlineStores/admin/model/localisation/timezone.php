<?php

class ModelLocalisationTimezone extends Model 
{
    public function getTimezones()
    {
        return array(
            'Asia/Kuwait'                                                           => '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg',
            'Etc/GMT+12'                                                            => '(GMT -12:00) Eniwetok, Kwajalein',
            'Pacific/Niue'                                                          => '(GMT -11:00) Midway Island, Samoa',
            'Pacific/Honolulu'                                                      => '(GMT -10:00) Hawaii',
            'Pacific/Marquesas'                                                     => '(GMT -9:30) Taiohae',
            'Pacific/Gambier'                                                       => '(GMT -9:00) Alaska',
            'America/Los_Angeles'                                                   => '(GMT -8:00) Pacific Time (US &amp; Canada)',
            'America/Boise'                                                         => '(GMT -7:00) Mountain Time (US &amp; Canada)',
            'America/Chicago'                                                       => '(GMT -6:00) Central Time (US &amp; Canada), Mexico City',
            'America/Detroit'                                                       => '(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima',
            'America/Caracas'                                                       => '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz',
            'America/St_Johns'                                                      => '(GMT -3:30) Newfoundland',
            'America/Argentina/Ushuaia'                                             => '(GMT -3:00) Brazil, Buenos Aires, Georgetown',
            'America/Noronha'                                                       => '(GMT -2:00) Mid-Atlantic',
            'America/Scoresbysund'                                                  => '(GMT -1:00) Azores, Cape Verde Islands',
            'Europe/London'                                                         => '(GMT +0:00) Western Europe Time, London, Lisbon, Casablanca',
            'Europe/Rome'                                                           => '(GMT +1:00) Brussels, Copenhagen, Madrid, Paris, Rome',
            'Africa/Cairo'                                                          => '(GMT +2:00) Cairo, Kaliningrad, South Africa',
            'Asia/Tehran'                                                           => '(GMT +3:30) Tehran',
            'Asia/Muscat'                                                           => '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi',
            'Asia/Kabul'                                                            => '(GMT +4:30) Kabul',
            'Asia/Tashkent'                                                         => '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
            'Asia/Kolkata'                                                          => '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi',
            'Asia/Kathmandu'                                                        => '(GMT +5:45) Kathmandu, Pokhara',
            'Indian/Chagos'                                                         => '(GMT +6:00) Almaty, Dhaka, Colombo',
            'Asia/Urumqi'                                                           => '(GMT +6:30) Yangon, Mandalay',
            'Indian/Christmas'                                                      => '(GMT +7:00) Bangkok, Hanoi, Jakarta',
            'Australia/Perth'                                                       => '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong',
            'Asia/Pyongyang'                                                        => '(GMT +8:30) Pyongyang',
            'Australia/Eucla'                                                       => '(GMT +8:45) Eucla',
            'Asia/Jayapura'                                                         => '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
            'Australia/Broken_Hill'                                                 => '(GMT +9:30) Adelaide, Darwin',
            'Australia/Sydney'                                                      => '(GMT +10:00) Eastern Australia, Guam, Vladivostok',
            'Australia/Lord_Howe'                                                   => '(GMT +10:30) Lord Howe Island',
            'Pacific/Kosrae'                                                        => '(GMT +11:00) Magadan, Solomon Islands, New Caledonia',
            'Pacific/Tarawa'                                                        => '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka',
            'Pacific/Chatham'                                                       => '(GMT +12:45) Chatham Islands',
            'Pacific/Fakaofo'                                                       => '(GMT +13:00) Apia, Nukualofa',
            'Pacific/Kiritimati'                                                    => '(GMT +14:00) Line Islands, Tokelau',
        );
    }
}