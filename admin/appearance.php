<?php
session_set_cookie_params(0, "/");
session_name("WPSD_Session");
session_id('wpsdsession');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
unset($_SESSION['WPSDdashConfig']);
unset($_SESSION['MMDVMHostConfigs']);
checkSessionValidity();

$displayType = getConfigItem("General", "Display", $_SESSION['MMDVMHostConfigs']);

$themes = [
    'light' => [
        'Background' => [
            'PageColor'=>'#F4F6F8', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#D1D5DB',
            'NavbarColor'=>'#007BFF', 'NavbarHoverColor'=>'#0056b3', 'DropdownColor'=>'#0069D9',
            'DropdownHoverColor'=>'#0056b3', 'ServiceCellActiveColor'=>'#28A745',
            'ServiceCellInactiveColor'=>'#DC3545', 'ModeCellDisabledColor'=>'#ADB5BD',
            'ModeCellActiveColor'=>'#28A745', 'ModeCellInactiveColor'=>'#DC3545',
            'ModeCellPausedColor'=>'#FFC107', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#EFF2F5', 'TableRowBgOddColor'=>'#E4E7EB'
        ],
        'Text' => [
            'TextColor'=>'#212529', 'TextSectionColor'=>'#495057', 'TextLinkColor'=>'#0056b3',
            'TableHeaderColor'=>'#212529', 'BannersColor'=>'#212529', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#495057', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#DEE2E6', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'dark' => [
        'Background' => [
            'PageColor'=>'#161A22', 'ContentColor'=>'#1D222C', 'BannersColor'=>'#242A36',
            'NavbarColor'=>'#242A36', 'NavbarHoverColor'=>'#4A90E2', 'DropdownColor'=>'#242A36',
            'DropdownHoverColor'=>'#4A90E2', 'ServiceCellActiveColor'=>'#33A753',
            'ServiceCellInactiveColor'=>'#D9534F', 'ModeCellDisabledColor'=>'#2A303B',
            'ModeCellActiveColor'=>'#33A753', 'ModeCellInactiveColor'=>'#D9534F',
            'ModeCellPausedColor'=>'#E8950E', 'NavPanelColor'=>'#1D222C',
            'TableRowBgEvenColor'=>'#202630', 'TableRowBgOddColor'=>'#1D222C'
        ],
        'Text' => [
            'TextColor'=>'#E0E0E0', 'TextSectionColor'=>'#A0A8B4', 'TextLinkColor'=>'#3B82F6',
            'TableHeaderColor'=>'#E0E0E0', 'BannersColor'=>'#E0E0E0', 'NavbarColor'=>'#E0E0E0',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#E0E0E0', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#E0E0E0',
            'ModeCellDisabledColor'=>'#8790A0', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#E0E0E0'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#303845', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'classic' => [
        'Background' => [
            'PageColor'=>'#212529', 'ContentColor'=>'#212529', 'BannersColor'=>'#2e363f',
            'NavbarColor'=>'#2e363f', 'NavbarHoverColor'=>'#65737e', 'DropdownColor'=>'#6b6c73',
            'DropdownHoverColor'=>'#3c3f47', 'ServiceCellActiveColor'=>'#2c7f2c',
            'ServiceCellInactiveColor'=>'#8C0C26', 'ModeCellDisabledColor'=>'#535353',
            'ModeCellActiveColor'=>'#2c7f2c', 'ModeCellInactiveColor'=>'#8C0C26',
            'ModeCellPausedColor'=>'#a65d14', 'NavPanelColor'=>'#212529',
            'TableRowBgEvenColor'=>'#949494', 'TableRowBgOddColor'=>'#7a7c80'
        ],
        'Text' => [
            'TextColor'=>'#000000', 'TextSectionColor'=>'#bebebe', 'TextLinkColor'=>'#1a2573',
            'TableHeaderColor'=>'#bebebe', 'BannersColor'=>'#bebebe', 'NavbarColor'=>'#bebebe',
            'NavbarHoverColor'=>'#ffffff', 'DropdownColor'=>'#ffffff', 'DropdownHoverColor'=>'#ffffff',
            'ServiceCellActiveColor'=>'#ffffff', 'ServiceCellInactiveColor'=>'#bebebe',
            'ModeCellDisabledColor'=>'#b3b3af', 'ModeCellActiveColor'=>'#ffffff',
            'ModeCellInactiveColor'=>'#bebebe'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#3c3f47', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'colorblind_focus' => [
        'Background' => [
            'PageColor'=>'#EAEAEA', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#B0C4DE',
            'NavbarColor'=>'#003366', 'NavbarHoverColor'=>'#0055A4', 'DropdownColor'=>'#004488',
            'DropdownHoverColor'=>'#0066CC', 'ServiceCellActiveColor'=>'#FFA500',
            'ServiceCellInactiveColor'=>'#778899', 'ModeCellDisabledColor'=>'#C0C0C0',
            'ModeCellActiveColor'=>'#FFA500', 'ModeCellInactiveColor'=>'#778899',
            'ModeCellPausedColor'=>'#FFD700', 'NavPanelColor'=>'#F0F0F0',
            'TableRowBgEvenColor'=>'#F5F5F5', 'TableRowBgOddColor'=>'#E8E8E8'
        ],
        'Text' => [
            'TextColor'=>'#1A1A1A', 'TextSectionColor'=>'#333333', 'TextLinkColor'=>'#0033A0',
            'TableHeaderColor'=>'#1A1A1A', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#000000', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#555555', 'ModeCellActiveColor'=>'#000000',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#AAAAAA', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'high_contrast' => [
        'Background' => [
            'PageColor'=>'#000000', 'ContentColor'=>'#111111', 'BannersColor'=>'#222222',
            'NavbarColor'=>'#000000', 'NavbarHoverColor'=>'#333333', 'DropdownColor'=>'#1C1C1C',
            'DropdownHoverColor'=>'#444444', 'ServiceCellActiveColor'=>'#00FF00',
            'ServiceCellInactiveColor'=>'#FF0000', 'ModeCellDisabledColor'=>'#555555',
            'ModeCellActiveColor'=>'#00FF00', 'ModeCellInactiveColor'=>'#FF0000',
            'ModeCellPausedColor'=>'#FFFF00', 'NavPanelColor'=>'#0A0A0A',
            'TableRowBgEvenColor'=>'#1A1A1A', 'TableRowBgOddColor'=>'#101010'
        ],
        'Text' => [
            'TextColor'=>'#FFFFFF', 'TextSectionColor'=>'#DDDDDD', 'TextLinkColor'=>'#00FFFF',
            'TableHeaderColor'=>'#FFFFFF', 'BannersColor'=>'#FFFFFF', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#000000', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#AAAAAA', 'ModeCellActiveColor'=>'#000000',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#666666', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'aqua_marine' => [
        'Background' => [
            'PageColor'=>'#E0F7FA', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#B2EBF2',
            'NavbarColor'=>'#00796B', 'NavbarHoverColor'=>'#004D40', 'DropdownColor'=>'#00897B',
            'DropdownHoverColor'=>'#00695C', 'ServiceCellActiveColor'=>'#4CAF50',
            'ServiceCellInactiveColor'=>'#EF5350', 'ModeCellDisabledColor'=>'#BDBDBD',
            'ModeCellActiveColor'=>'#4CAF50', 'ModeCellInactiveColor'=>'#EF5350',
            'ModeCellPausedColor'=>'#FFC107', 'NavPanelColor'=>'#F0FEFF',
            'TableRowBgEvenColor'=>'#E0F2F1', 'TableRowBgOddColor'=>'#C8E6C9'
        ],
        'Text' => [
            'TextColor'=>'#263238', 'TextSectionColor'=>'#004D40', 'TextLinkColor'=>'#006064',
            'TableHeaderColor'=>'#004D40', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#424242', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#B2DFDB', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'warm_ember' => [
        'Background' => [
            'PageColor'=>'#FFF3E0', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#FFCCBC',
            'NavbarColor'=>'#E65100', 'NavbarHoverColor'=>'#BF360C', 'DropdownColor'=>'#F57C00',
            'DropdownHoverColor'=>'#D84315', 'ServiceCellActiveColor'=>'#43A047',
            'ServiceCellInactiveColor'=>'#D32F2F', 'ModeCellDisabledColor'=>'#BCAAA4',
            'ModeCellActiveColor'=>'#43A047', 'ModeCellInactiveColor'=>'#D32F2F',
            'ModeCellPausedColor'=>'#FFB300', 'NavPanelColor'=>'#FFF8E1',
            'TableRowBgEvenColor'=>'#FFE0B2', 'TableRowBgOddColor'=>'#FFCC80'
        ],
        'Text' => [
            'TextColor'=>'#4E342E', 'TextSectionColor'=>'#BF360C', 'TextLinkColor'=>'#D84315',
            'TableHeaderColor'=>'#4E342E', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#5D4037', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#D7CCC8', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'earthy_canopy' => [
        'Background' => [
            'PageColor'=>'#E8F5E9', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#C8E6C9',
            'NavbarColor'=>'#38761D', 'NavbarHoverColor'=>'#274E13', 'DropdownColor'=>'#558B2F',
            'DropdownHoverColor'=>'#33691E', 'ServiceCellActiveColor'=>'#689F38',
            'ServiceCellInactiveColor'=>'#A1887F', 'ModeCellDisabledColor'=>'#A5D6A7',
            'ModeCellActiveColor'=>'#689F38', 'ModeCellInactiveColor'=>'#A1887F',
            'ModeCellPausedColor'=>'#FBC02D', 'NavPanelColor'=>'#F1F8E9',
            'TableRowBgEvenColor'=>'#DCEDC8', 'TableRowBgOddColor'=>'#C5E1A5'
        ],
        'Text' => [
            'TextColor'=>'#3E2723', 'TextSectionColor'=>'#274E13', 'TextLinkColor'=>'#33691E',
            'TableHeaderColor'=>'#3E2723', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#556B2F', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#C8E6C9', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'monochrome_accent' => [
        'Background' => [
            'PageColor'=>'#ECEFF1', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#CFD8DC',
            'NavbarColor'=>'#37474F', 'NavbarHoverColor'=>'#263238', 'DropdownColor'=>'#455A64',
            'DropdownHoverColor'=>'#263238', 'ServiceCellActiveColor'=>'#00ACC1',
            'ServiceCellInactiveColor'=>'#78909C', 'ModeCellDisabledColor'=>'#B0BEC5',
            'ModeCellActiveColor'=>'#00ACC1', 'ModeCellInactiveColor'=>'#78909C',
            'ModeCellPausedColor'=>'#FFB300', 'NavPanelColor'=>'#FAFAFA',
            'TableRowBgEvenColor'=>'#F5F5F5', 'TableRowBgOddColor'=>'#EEEEEE'
        ],
        'Text' => [
            'TextColor'=>'#212121', 'TextSectionColor'=>'#546E7A', 'TextLinkColor'=>'#00838F',
            'TableHeaderColor'=>'#212121', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#424242', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#CFD8DC', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'vintage_vibes' => [
        'Background' => [
            'PageColor'=>'#FDF5E6', 'ContentColor'=>'#FAF0E6', 'BannersColor'=>'#D2B48C',
            'NavbarColor'=>'#800000', 'NavbarHoverColor'=>'#A52A2A', 'DropdownColor'=>'#BC8F8F',
            'DropdownHoverColor'=>'#8B4513', 'ServiceCellActiveColor'=>'#2E8B57',
            'ServiceCellInactiveColor'=>'#CD5C5C', 'ModeCellDisabledColor'=>'#A9A9A9',
            'ModeCellActiveColor'=>'#2E8B57', 'ModeCellInactiveColor'=>'#CD5C5C',
            'ModeCellPausedColor'=>'#FFA500', 'NavPanelColor'=>'#F5F5DC',
            'TableRowBgEvenColor'=>'#F0E68C', 'TableRowBgOddColor'=>'#E6E6FA'
        ],
        'Text' => [
            'TextColor'=>'#553100', 'TextSectionColor'=>'#8B4513', 'TextLinkColor'=>'#800000',
            'TableHeaderColor'=>'#553100', 'BannersColor'=>'#FFFFFF', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#000000', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#FFFFFF',
            'ModeCellDisabledColor'=>'#FFFFFF', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#FFFFFF'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#D2B48C', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'ocean_breeze' => [
        'Background' => [
            'PageColor'=>'#CAF0F8', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#ADE8F4',
            'NavbarColor'=>'#0077B6', 'NavbarHoverColor'=>'#005A8E', 'DropdownColor'=>'#0096C7',
            'DropdownHoverColor'=>'#0077B6', 'ServiceCellActiveColor'=>'#48CAE4',
            'ServiceCellInactiveColor'=>'#90E0EF', 'ModeCellDisabledColor'=>'#BDC3C7',
            'ModeCellActiveColor'=>'#48CAE4', 'ModeCellInactiveColor'=>'#90E0EF',
            'ModeCellPausedColor'=>'#00B4D8', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#E0F7FA', 'TableRowBgOddColor'=>'#CAF0F8'
        ],
        'Text' => [
            'TextColor'=>'#2C3E50', 'TextSectionColor'=>'#0077B6', 'TextLinkColor'=>'#005A8E',
            'TableHeaderColor'=>'#2C3E50', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#000000', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#FFFFFF', 'ModeCellActiveColor'=>'#000000',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#90E0EF', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'sunset_glow' => [
        'Background' => [
            'PageColor'=>'#FFF1E6', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#FFDAB9',
            'NavbarColor'=>'#FF6B6B', 'NavbarHoverColor'=>'#E64A4A', 'DropdownColor'=>'#FF8C42',
            'DropdownHoverColor'=>'#FF6B6B', 'ServiceCellActiveColor'=>'#FFAD60',
            'ServiceCellInactiveColor'=>'#FFD166', 'ModeCellDisabledColor'=>'#FFE4B5',
            'ModeCellActiveColor'=>'#FFAD60', 'ModeCellInactiveColor'=>'#FFD166',
            'ModeCellPausedColor'=>'#FFA500', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#FFF8DC', 'TableRowBgOddColor'=>'#FFF1E6'
        ],
        'Text' => [
            'TextColor'=>'#5D4037', 'TextSectionColor'=>'#E65100', 'TextLinkColor'=>'#BF360C',
            'TableHeaderColor'=>'#5D4037', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#000000', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#000000', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#5D4037', 'ModeCellActiveColor'=>'#000000',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#FFCCBC', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'forest_whisper' => [
        'Background' => [
            'PageColor'=>'#D8F3DC', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#B7E4C7',
            'NavbarColor'=>'#2D6A4F', 'NavbarHoverColor'=>'#1B4332', 'DropdownColor'=>'#40916C',
            'DropdownHoverColor'=>'#2D6A4F', 'ServiceCellActiveColor'=>'#52B788',
            'ServiceCellInactiveColor'=>'#95D5B2', 'ModeCellDisabledColor'=>'#D2B48C',
            'ModeCellActiveColor'=>'#52B788', 'ModeCellInactiveColor'=>'#95D5B2',
            'ModeCellPausedColor'=>'#74C69D', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#EDF7ED', 'TableRowBgOddColor'=>'#D8F3DC'
        ],
        'Text' => [
            'TextColor'=>'#3E2723', 'TextSectionColor'=>'#2D6A4F', 'TextLinkColor'=>'#1B4332',
            'TableHeaderColor'=>'#3E2723', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#000000', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#3E2723', 'ModeCellActiveColor'=>'#000000',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#B7E4C7', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'lavender_dream' => [
        'Background' => [
            'PageColor'=>'#E6E6FA', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#D8BFD8',
            'NavbarColor'=>'#6A0DAD', 'NavbarHoverColor'=>'#4B0082', 'DropdownColor'=>'#9370DB',
            'DropdownHoverColor'=>'#6A0DAD', 'ServiceCellActiveColor'=>'#BA55D3',
            'ServiceCellInactiveColor'=>'#DDA0DD', 'ModeCellDisabledColor'=>'#D3D3D3',
            'ModeCellActiveColor'=>'#BA55D3', 'ModeCellInactiveColor'=>'#DDA0DD',
            'ModeCellPausedColor'=>'#B19CD9', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#F8F0FC', 'TableRowBgOddColor'=>'#E6E6FA'
        ],
        'Text' => [
            'TextColor'=>'#483D8B', 'TextSectionColor'=>'#6A0DAD', 'TextLinkColor'=>'#4B0082',
            'TableHeaderColor'=>'#483D8B', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#483D8B', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#D8BFD8', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ],
    'steel_sky' => [
        'Background' => [
            'PageColor'=>'#ECF0F1', 'ContentColor'=>'#FFFFFF', 'BannersColor'=>'#BDC3C7',
            'NavbarColor'=>'#2C3E50', 'NavbarHoverColor'=>'#1A252F', 'DropdownColor'=>'#34495E',
            'DropdownHoverColor'=>'#2C3E50', 'ServiceCellActiveColor'=>'#2980B9',
            'ServiceCellInactiveColor'=>'#95A5A6', 'ModeCellDisabledColor'=>'#B0BEC5',
            'ModeCellActiveColor'=>'#2980B9', 'ModeCellInactiveColor'=>'#95A5A6',
            'ModeCellPausedColor'=>'#F39C12', 'NavPanelColor'=>'#FFFFFF',
            'TableRowBgEvenColor'=>'#F5F7F8', 'TableRowBgOddColor'=>'#ECF0F1'
        ],
        'Text' => [
            'TextColor'=>'#34495E', 'TextSectionColor'=>'#2C3E50', 'TextLinkColor'=>'#1A252F',
            'TableHeaderColor'=>'#34495E', 'BannersColor'=>'#000000', 'NavbarColor'=>'#FFFFFF',
            'NavbarHoverColor'=>'#FFFFFF', 'DropdownColor'=>'#FFFFFF', 'DropdownHoverColor'=>'#FFFFFF',
            'ServiceCellActiveColor'=>'#FFFFFF', 'ServiceCellInactiveColor'=>'#000000',
            'ModeCellDisabledColor'=>'#2C3E50', 'ModeCellActiveColor'=>'#FFFFFF',
            'ModeCellInactiveColor'=>'#000000'
        ],
        'ExtraSettings' => [
            'TableBorderColor'=>'#BDC3C7', 'LastHeardRows'=>'40', 'MainFontSize'=>'18',
            'HeaderFontSize'=>'34', 'BodyFontSize'=>'17'
        ]
    ]
];

$filepath_ini = '/etc/wpsd-css.ini';
$parsed_ini = null;
$use_classic_default_for_ini_file = false;

if (file_exists($filepath_ini)) {
    $parsed_ini_content = parse_ini_file($filepath_ini, true);
    if ($parsed_ini_content === false || empty($parsed_ini_content)) {
        error_log("WPSD Dashboard: /etc/wpsd-css.ini is unparseable or empty. Using classic theme as default and attempting to recreate file.");
        $use_classic_default_for_ini_file = true;
    } else {
        $parsed_ini = $parsed_ini_content;
    }
} else {
    error_log("WPSD Dashboard: /etc/wpsd-css.ini not found. Using classic theme as default and creating file.");
    $use_classic_default_for_ini_file = true;
}

if ($use_classic_default_for_ini_file) {
    if (isset($themes['classic'])) {
        $parsed_ini = $themes['classic'];

        $content = "";
        foreach ($themes['classic'] as $section => $values) {
            if (!is_array($values)) continue;
            $content .= "[" . $section . "]\n";
            foreach ($values as $key => $value) {
                $content .= $key . "=" . $value . "\n";
            }
            $content .= "\n";
        }

        $temp_ini_path = "/tmp/wpsd_default_classic.ini";
        if (file_put_contents($temp_ini_path, $content) !== false) {
            exec('sudo cp ' . escapeshellarg($temp_ini_path) . ' ' . escapeshellarg($filepath_ini));
            exec('sudo chmod 644 ' . escapeshellarg($filepath_ini));
            exec('sudo chown root:root ' . escapeshellarg($filepath_ini));
            error_log("WPSD Dashboard: /etc/wpsd-css.ini has been created/overwritten with classic theme values.");
        } else {
            error_log("WPSD Dashboard: Failed to write temporary INI file for classic default at " . $temp_ini_path);
        }
    } else {
        error_log("WPSD Dashboard: CRITICAL - 'classic' theme is not defined in \$themes array. Cannot set default INI.");
        $parsed_ini = [];
    }
}

if (!is_array($parsed_ini)) {
    error_log("WPSD Dashboard: \$parsed_ini could not be initialized from file or classic theme. Defaulting to empty array.");
    if(isset($themes['classic'])) {
        $parsed_ini = $themes['classic'];
    } else {
        $parsed_ini = [];
    }
}


function compare_color_settings($config_colors, $theme_colors) {
    if (!is_array($config_colors) || !is_array($theme_colors)) {
        return is_array($config_colors) === is_array($theme_colors) && empty($config_colors) && empty($theme_colors);
    }
    if (count($config_colors) !== count($theme_colors)) {
        return false;
    }
    return empty(array_diff_assoc($config_colors, $theme_colors));
}

$ini_background_colors = isset($parsed_ini['Background']) ? $parsed_ini['Background'] : [];
$ini_text_colors = isset($parsed_ini['Text']) ? $parsed_ini['Text'] : [];
$selected_theme_on_load = "custom";

foreach ($themes as $theme_key => $theme_data) {
    $theme_background_colors = isset($theme_data['Background']) ? $theme_data['Background'] : [];
    $theme_text_colors = isset($theme_data['Text']) ? $theme_data['Text'] : [];

    if (compare_color_settings($ini_background_colors, $theme_background_colors) &&
        compare_color_settings($ini_text_colors, $theme_text_colors)) {
        $selected_theme_on_load = $theme_key;
        break;
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
    <head>
        <meta name="language" content="English" />
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="pragma" content="no-cache" />
        <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
        <meta http-equiv="Expires" content="0" />
        <title>WPSD Dashboard - Appearance Settings</title>
        <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript" src="/css/farbtastic/farbtastic.min.js?version=<?php echo $versionCmd; ?>"></script>
        <link rel="stylesheet" type="text/css" href="/css/farbtastic/farbtastic.css" />
        <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
        <style type="text/css" media="screen">
            .colorwell {
                border: 2px solid #fff;
                width: 6em;
                text-align: center;
                cursor: pointer;
            }
            body .colorwell-selected {
                border: 2px solid #000;
                font-weight: bold;
            }
        </style>
        <script type="text/javascript">
            const WPSD_THEMES = <?php echo json_encode($themes); ?>;
            const WPSD_CURRENT_CONFIG_FROM_INI = <?php echo json_encode($parsed_ini); ?>;
            const INITIAL_SELECTED_THEME_KEY = <?php echo json_encode($selected_theme_on_load); ?>;

            function cssDownload() {
                window.location.href = "/admin/advanced/css_download.php";
            }

            function cssUpload() {
                document.getElementById('fileid').addEventListener('change', submitForm);
                document.getElementById('fileid').click();
            }

            function submitForm() {
                document.getElementById('cssUpload').submit();
            }

            function cssReset() {
                if (confirm('WARNING: This will reset all appearance settings to the "Classic" theme and apply them. Your current unsaved customizations will be lost.\n\nAre you SURE you want to do this?\n\nPress OK to restore and apply the Classic theme.\nPress Cancel to go back.')) {
                    $('#themeSelector').val('classic').triggerHandler('change');
                    setTimeout(function() {
                        document.forms['edit-css'].submit();
                    }, 100);
                } else {
                    return false;
                }
            }

            $(document).ready(function() {
                var f = $.farbtastic('#colorpicker');
                var p = $('#colorpicker').css('opacity', 1).hide();
                var selected;

                function getContrastColor(hexcolor){
                    if (!hexcolor || typeof hexcolor !== 'string' || hexcolor.toLowerCase() === 'none') return '#000000'; 
                    let hex = hexcolor.replace("#", "");
                    if (hex.length === 3) {
                        hex = hex.split('').map(char => char + char).join('');
                    }
                    if (hex.length !== 6) return '#000000'; 

                    const r = parseInt(hex.substr(0,2),16);
                    const g = parseInt(hex.substr(2,2),16);
                    const b = parseInt(hex.substr(4,2),16);
                    if (isNaN(r) || isNaN(g) || isNaN(b)) return '#000000'; 

                    const yiq = ((r*299)+(g*587)+(b*114))/1000;
                    return (yiq >= 128) ? '#000000' : '#FFFFFF';
                }

                $('.colorwell')
                    .each(function () {
                        $(this).css('background-color', $(this).val()); 
                        $(this).css('color', getContrastColor($(this).val())); 
                        $(this).css('opacity', 1);
                    })
                    .focus(function() {
                        if (selected) {
                            $(selected).removeClass('colorwell-selected');
                        }
                        f.linkTo(this);
                        p.show();
                        selected = this;
                        $(this).addClass('colorwell-selected');
                    })
                    .on('change keyup input', function() { 
                        $(this).css('background-color', $(this).val()); 
                        $(this).css('color', getContrastColor($(this).val())); 
                    });

                $(document).mousedown(function(event) {
                    if (!$(event.target).closest('#colorpicker').length && !$(event.target).is('.colorwell')) {
                        if (p.is(":visible")) {
                            p.hide();
                            if (selected) {
                                $(selected).removeClass('colorwell-selected');
                                selected = null;
                            }
                        }
                    }
                });
                
                $('#themeSelector').val(INITIAL_SELECTED_THEME_KEY);

                $('#themeSelector').change(function() {
                    const selectedThemeName = $(this).val();
                    let sourceData;
                    let isPredefinedTheme = false;

                    if (selectedThemeName === 'custom') {
                        sourceData = WPSD_CURRENT_CONFIG_FROM_INI;
                        isPredefinedTheme = false;
                    } else if (selectedThemeName && WPSD_THEMES[selectedThemeName]) {
                        sourceData = WPSD_THEMES[selectedThemeName];
                        isPredefinedTheme = true;
                    } else {
                        return; 
                    }
                   
                    for (const section in sourceData) {
                       if (sourceData.hasOwnProperty(section)) {
                           for (const key in sourceData[section]) {
                               if (sourceData[section].hasOwnProperty(key)) {
                                   const inputName = section + '[' + key + ']';
                                   const inputValue = sourceData[section][key];
                                   const inputElement = $('input[name="' + inputName + '"]');

                                   if (inputElement.length) {
                                       inputElement.val(inputValue);
                                       if (inputElement.hasClass('colorwell')) {
                                           inputElement.css('background-color', inputValue);
                                           inputElement.css('color', getContrastColor(inputValue));
                                           if (selected && inputElement.is(selected)) {
                                               f.setColor(inputValue);
                                           }
                                       }
                                       if (isPredefinedTheme) {
                                           inputElement.triggerHandler('change'); 
                                       }
                                   }
                               }
                           }
                       }
                    }
                });

                if (INITIAL_SELECTED_THEME_KEY !== "" && INITIAL_SELECTED_THEME_KEY !== null) {
                   $('#themeSelector').triggerHandler('change');
                }
            });
        </script>
    </head>
    <body>
        <div class="container">
            <?php include $_SERVER['DOCUMENT_ROOT'].'/admin/advanced/header-menu.inc'; ?>
            <div class="contentwide">
                <?php
                $filepath = '/tmp/bW1kd4jg6b3N0DQo.tmp';

                if (empty($_POST['CallLookupProvider']) != TRUE) {
                    exec('sudo sed -i "/CallLookupProvider = /c\\\CallLookupProvider = '.escapeshellcmd($_POST['CallProvider']).'" ' . $config_file . '');    
                    unset($_POST);
                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
                    die();
                }
                if (isset($_POST['phoneticCallsigns'])) {
                    $phoneticCallsigns = escapeshellcmd($_POST['phoneticCallsigns']);
                    $output = shell_exec("grep -c '^PhoneticCallsigns =' $config_file");
                    if (trim($output) == '0') {
                        exec("echo 'PhoneticCallsigns = $phoneticCallsigns' | sudo tee -a $config_file > /dev/null");
                    } else {
                        exec("sudo sed -i '/PhoneticCallsigns = /c\\PhoneticCallsigns = $phoneticCallsigns' $config_file");
                    }
                    unset($_POST);
                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
                    die();
                }

                if (file_exists($filepath_ini)) {
                    exec('sudo cp '.$filepath_ini.' '.$filepath);
                    exec('sudo chown www-data:www-data '.$filepath);
                    exec('sudo chmod 664 '.$filepath);
                }

                if($_POST) {
                    $data = $_POST;
                    if (empty($_POST['cssDownload']) != TRUE) {
                    } else if (empty($_POST['cssUpload']) != TRUE) {
                        echo "<tr><th colspan=\"2\">Appearance upload and apply...</th></tr>\n";
                        if (isset($_FILES['cssFile']) && $_FILES['cssFile']['error'] === UPLOAD_ERR_OK) {
                            $output = "Uploading your appearance settings data.\n";
                            $target_dir = "/tmp/css_restore/";
                            $okay = false;
                            shell_exec("sudo rm -rf $target_dir 2>&1");
                            shell_exec("mkdir $target_dir 2>&1");
                            if($_FILES["cssFile"]["name"]) {
                                $filename = $_FILES["cssFile"]["name"];
                                $source = $_FILES["cssFile"]["tmp_name"];
                                $type = $_FILES["cssFile"]["type"];
                                $name = explode(".", $filename);
                                $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
                                foreach($accepted_types as $mime_type) {
                                    if($mime_type == $type) {
                                        $okay = true;
                                        break;
                                    }
                                }
                            }
                            $continue = false;
                            if (isset($name)) {
                                $continue = strtolower($name[1]) == 'zip' ? true : false;
                            }
                            if ($okay == false || $continue == false) {
                                $output .= "The file you are trying to upload is not a .zip file. Please try again.\n";
                                echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
                            } else {
                                if (isset($filename)) {
                                    $target_path = $target_dir.$filename;
                                }
                                if(isset($target_path) && move_uploaded_file($source, $target_path)) {
                                    $zip = new ZipArchive();
                                    $x = $zip->open($target_path);
                                    if ($x === true) {
                                        $zip->extractTo($target_dir);
                                        $zip->close();
                                        unlink($target_path);
                                    }
                                    $output .= "Your .zip file was uploaded and unpacked.\n";
                                    $output .= "Copying appearance setttings...\n";
                                    $output .= shell_exec("sudo mv -v -f /tmp/css_restore/wpsd-css.ini ".$filepath_ini." 2>&1")."\n";
                                    $output .= "Appearance Restoration Complete.\n";
                                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;}, 4000);</script>';
                                } else {
                                    $output .= "There was a problem with the upload. Please try again.<br />";
                                    $output .= "\n".'<button onclick="goBack()">Go Back</button><br />'."\n";
                                    $output .= '<script>'."\n";
                                    $output .= 'function goBack() {'."\n";
                                    $output .= '    window.history.back();'."\n";
                                    $output .= '}'."\n";
                                    $output .= '</script>'."\n";
                                }
                                echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
                            }
                        } else {
                            echo "<tr><td align=\"left\"><pre>No file uploaded or an error occurred.</pre></td></tr>\n";
                        }
                    } else {
                        if (update_ini_file($data, $filepath)) {
                            exec('sudo cp '.$filepath.' '.$filepath_ini);
                            exec('sudo chmod 644 '.$filepath_ini);
                            exec('sudo chown root:root '.$filepath_ini);
                            echo '<script type="text/javascript">window.location=window.location.href.split("?")[0];</script>';
                            die();
                        } else {
                            echo "Error updating INI file."; 
                        }
                    }
                }

                function update_ini_file($data, $filepath_to_update) {
                    $content = "";
                    foreach($data as $section=>$values) {
                        if (!is_array($values)) continue;
                        $section = str_replace("_", " ", $section);
                        $content .= "[".$section."]\n";
                        foreach($values as $key=>$value) {
                            if ($value == '') {
                                $content .= $key."=none\n";
                            } else {
                                $content .= $key."=".$value."\n";
                            }
                        }
                        $content .= "\n";
                    }
                    if (!$handle = fopen($filepath_to_update, 'w')) {
                        return false;
                    }
                    $success = fwrite($handle, $content);
                    fclose($handle);
                    return $success;
                }
                ?>

                <?php if ($displayType == "OLED") { ?>
                <h2 class="ConfSec">OLED Display Control</h2>
                <script>
                    function toggleOLED() {
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', '/admin/OLED_ajax.php?action=toggle', true);
                        xhr.send();
                    }
                </script>
                <table>
                    <tr>
                        <td class="left">
                            <button id="toggleButton" onclick="toggleOLED()">Toggle OLED Display Off/On</button>
                        </td>
                    </tr>
                </table>
                <?php } ?>

                <br />

                <h2 class="ConfSec">Callsign Link Provider</h2>    
                <table>
                    <tr>
                        <td>
                            <form method="post" action="" class="left">
                                <input type="radio" name="CallProvider" value="RadioID" id="RadioID" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider']) && $_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider'] == "RadioID") {  echo 'checked="checked"'; } ?> />
                                <label for="RadioID">RadioID</label>
                                &nbsp;
                                <input type="radio" name="CallProvider" value="QRZ" id="QRZ" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider']) && $_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider'] == "QRZ") {  echo 'checked="checked"'; } ?> />
                                <label for="QRZ">QRZ</label>
                                &nbsp;
                                <input name="CallLookupProvider" type="submit" value="Apply Change" />
                            </form>
                        </td>
                    </tr>
                </table>
                <br>
                <h2 class="ConfSec">Phonetic Callsigns</h2>
                <table>
                    <tr>
                        <td>
                            <form method="post" action="" class="left">
                                <input type="radio" name="phoneticCallsigns" value="0" id="phoneticCallsign-false" <?php if (!isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) || (isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) && $_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns'] == "0")) {  echo 'checked="checked"'; } ?> />
                                <label for="phoneticCallsign-false">Disabled</label>
                                &nbsp;
                                <input type="radio" name="phoneticCallsigns" value="1" id="phoneticCallsign-true" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) && $_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns'] == "1") {  echo 'checked="checked"'; } ?> />
                                <label for="phoneticCallsign-true">Enabled</label>
                                &nbsp;
                                <input name="phoneticCallsignsSubmit" type="submit" value="Apply Change" />
                            </form>
                        </td>
                        <td align="left" style='word-wrap: break-word;white-space: normal;padding-left: 5px;'><i class="fa fa-question-circle"></i> When enabled an additional label will be displayed with the phonetic version of callsigns</td>
                    </tr>
                </table>    
                <br />
    
                <h2 class="ConfSec">Appearance and Extra Look/Feel Settings</h2>
                <?php
                    echo '<form action="" method="post" name="edit-css">'."\n"; 
                ?>
                <table>
                    <tr>
                        <th class="larger" colspan="3">Themes</th> 
                    </tr>
                    <tr>
                        <td align="right" style='padding-left:10em;width:150px;'>Select Theme:</td>
                        <td align="left">
                            <select id="themeSelector">
                                <option value="" disabled>-- Select a Theme --</option>
                                <?php
                                $theme_display_names = [
                                    'light' => 'WPSD Light',
                                    'dark' => 'WPSD Dark',
                                    'classic' => 'WPSD Classic',
                                    'colorblind_focus' => 'Blue &amp; Yellow Focus (Color Blind Friendly)',
                                    'high_contrast' => 'Midnight &amp; Snow (High Contrast)',
                                    'aqua_marine' => 'Aqua Marine',
                                    'warm_ember' => 'Warm Ember',
                                    'earthy_canopy' => 'Earthy Canopy',
                                    'monochrome_accent' => 'Monochrome Accent',
                                    'vintage_vibes' => 'Vintage Vibes',
                                    'ocean_breeze' => 'Ocean Breeze',
                                    'sunset_glow' => 'Sunset Glow',
                                    'forest_whisper' => 'Forest Whisper',
                                    'lavender_dream' => 'Lavender Dream',
                                    'steel_sky' => 'Steel &amp; Sky'
                                ];

                                if ($selected_theme_on_load === 'custom') {
                                    echo '<option value="custom" selected>Custom Configuration</option>';
                                }

                                foreach ($themes as $key => $theme_data_loop):
                                    $display_name = isset($theme_display_names[$key]) ? $theme_display_names[$key] : ucwords(str_replace('_', ' ', $key));
                                    $selected_attr = ($selected_theme_on_load === $key) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($key) . '" ' . $selected_attr . '>' . $display_name . '</option>';
                                endforeach;
                                ?>
                            </select>
                        </td>
                        <td align="left" style='word-wrap: break-word;white-space: normal;padding-left: 5px;'><i class="fa fa-info-circle"></i> Hint: You can apply the themes as-is, and/or customize them further below.</td>
                    </tr>
                    <tr>
                        <td></td> 
                        <td align="left" colspan="2" style="padding-top: 10px;">
                            <?php echo '<input type="submit" value="'.__( 'Apply Themne' ).'" />'."\n"; ?>
                        </td>
                    </tr>
                </table>

                <h3 class="ConfSec">Customize Theme</h3>

                <?php
                echo '<div style="position: fixed; pointer-events: none; transform: translateX(230%);" >'."\n";
                echo '    <div id="colorpicker" style="float: right; margin: 20px; pointer-events: auto;"></div>'."\n";
                echo '</div>'."\n";
                
                foreach($parsed_ini as $section=>$values_in_section) {
                    echo "<table>\n";
                    echo "    <tr><th class='larger' colspan=\"3\">".htmlspecialchars($section)."</th></tr>\n";
                    if (is_array($values_in_section)) {
                        foreach($values_in_section as $key=>$value) {
                            $key_display = htmlspecialchars($key);
                            $value_display = htmlspecialchars($value);
                            $section_name_for_input = htmlspecialchars($section);
                            $key_name_for_input = htmlspecialchars($key);

                            if (endsWith($key, 'SectionColor')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the small section heading font color; default is \"#000000\" [black].)</td></tr>\n";
                            } elseif ($key == 'TextColor') {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Main Content font color, used across most of the Dashboard's informational/data text; default is \"#000000\" [black].)</td></tr>\n";
                            } elseif (endsWith($key, 'Color')) { 
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\" colspan='2'><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td></tr>\n";
                            } elseif (startsWith($key, 'MainFontSize')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Main Content font size, in pixels, used across most of the Dashboard's informational/data text; default is 18 pixels.)</td></tr>\n";
                            } elseif (startsWith($key, 'BodyFontSize')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Body font size, in pixels, used across most of the Dashboard's non-data/non-informational text; default is 17 pixels.)</td></tr>\n";
                            } elseif (startsWith($key, 'HeaderFont')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Header font size, in pixels; default is 34 pixels.)</td></tr>\n";
                            } elseif (endsWith($key, 'HeardRows')) {
                                echo "    <tr><td align=\"right\" style='padding-left:15em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='3' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(The number of rows displayed on the Dashboard; default is 40 rows, and 100 rows is the maximum allowed.*)</td></tr>\n";
                            } else {
                                echo "    <tr><td align=\"right\" style='padding-left:15em;width:150px;'>$key_display</td><td align=\"left\" colspan='2'><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td></tr>\n";
                            }
                        }
                    }
                    echo "    <tr>\n";
                    echo "        <td></td>\n"; 
                    echo "        <td colspan=\"2\" align=\"left\" style=\"padding-top: 10px;\">\n";
                    echo "            <input type=\"submit\" value=\"".__( 'Apply Changes' )."\" />\n";
                    echo "        </td>\n";
                    echo "    </tr>\n";
                    echo "</table>\n";
                    echo "<br />\n"; 
                }
                echo "</form>\n";
                echo "<p> * Because of the way MMDVMHost logs last heard data, it is not guaranteed that the number of rows specified will be displayed.</p>\n";
                echo "<hr />\n";
                echo '<form id="cssUpload" action="" method="POST" enctype="multipart/form-data">'."\n";
                echo '    <div><input id="fileid" name="cssFile" type="file" hidden/></div>'."\n";
                echo '    <div><input type="hidden" name="cssUpload" value="1" /></div>'."\n";
                echo '</form>'."\n";
                echo '<input type="button" onclick="javascript:cssDownload();" value="Download Appearance Settings" />'."\n";
                echo '<input type="button" onclick="javascript:cssUpload();" value="Upload &amp; Apply Appearance Settings (zip file only!)" />'."\n";
                echo '<input style="background:crimson;color:white;" type="button" onclick="cssReset();" value="Reset to WPSD Classic Theme" />'."\n";
                echo "<br />\n";
                echo "<br />\n";
                ?>
            </div>
            <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?> 
        </div>
    </body>
</html>
