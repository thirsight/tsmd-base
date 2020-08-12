<?php

namespace tsmd\base\helpers;

/*******************************************************************************************************
 * class CountryArray
 * returns array of countries, can return flat array using get() function and 2d array with get2d()
 * it can provide following information related to  the country
 *      1) name
 *      2) alpha2 code, 2 characters (ISO-3166-1 alpha2)
 *      3) alpha3 code, 3 characters (ISO-3166-1 alpha3)
 *      4) numeric code (ISO-3166-1 numeric)
 *      5) ISD code for country
 *      6) continent
 *
 * Author : Sameer Shelavale
 * Email : samiirds@gmail.com, sameer@techrevol.com, sameer@possible.in
 * Author website: http://techrevol.com, http://possible.in
 * Phone : +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * Copyrights (C) Sameer Shelavale
 *******************************************************************************************************/

class CountryHelper
{
    public static $countries = [
        "AF" => ['alpha2' => 'AF', 'alpha3' => 'AFG', 'num' =>'004', 'isd' => '93', "continent" => "Asia", "en" => "Afghanistan", "zh-CN" => "阿富汗"],
        "AX" => ['alpha2' => 'AX', 'alpha3' => 'ALA', 'num' =>'248', 'isd' => '358', "continent" => "Europe", "en" => "Åland Islands", "zh-CN" => "奥兰群岛"],
        "AL" => ['alpha2' => 'AL', 'alpha3' => 'ALB', 'num' =>'008', 'isd' => '355', "continent" => "Europe", "en" => "Albania", "zh-CN" => "阿尔巴尼亚"],
        "DZ" => ['alpha2' => 'DZ', 'alpha3' => 'DZA', 'num' =>'012', 'isd' => '213', "continent" => "Africa", "en" => "Algeria", "zh-CN" => "阿尔及利亚"],
        "AS" => ['alpha2' => 'AS', 'alpha3' => 'ASM', 'num' =>'016', 'isd' => '1684', "continent" => "Oceania", "en" => "American Samoa", "zh-CN" => "美属萨摩亚"],
        "AD" => ['alpha2' => 'AD', 'alpha3' => 'AND', 'num' =>'020', 'isd' => '376', "continent" => "Europe", "en" => "Andorra", "zh-CN" => "安道尔"],
        "AO" => ['alpha2' => 'AO', 'alpha3' => 'AGO', 'num' =>'024', 'isd' => '244', "continent" => "Africa", "en" => "Angola", "zh-CN" => "安哥拉"],
        "AI" => ['alpha2' => 'AI', 'alpha3' => 'AIA', 'num' =>'660', 'isd' => '1264', "continent" => "North America", "en" => "Anguilla", "zh-CN" => "安圭拉"],
        "AQ" => ['alpha2' => 'AQ', 'alpha3' => 'ATA', 'num' =>'010', 'isd' => '672', "continent" => "Antarctica", "en" => "Antarctica", "zh-CN" => "南极洲"],
        "AG" => ['alpha2' => 'AG', 'alpha3' => 'ATG', 'num' =>'028', 'isd' => '1268', "continent" => "North America", "en" => "Antigua and Barbuda", "zh-CN" => "安提瓜和巴布达"],
        "AR" => ['alpha2' => 'AR', 'alpha3' => 'ARG', 'num' =>'032', 'isd' => '54', "continent" => "South America", "en" => "Argentina", "zh-CN" => "阿根廷"],
        "AM" => ['alpha2' => 'AM', 'alpha3' => 'ARM', 'num' =>'051', 'isd' => '374', "continent" => "Asia", "en" => "Armenia", "zh-CN" => "亚美尼亚"],
        "AW" => ['alpha2' => 'AW', 'alpha3' => 'ABW', 'num' =>'533', 'isd' => '297', "continent" => "North America", "en" => "Aruba", "zh-CN" => "阿鲁巴"],
        "AU" => ['alpha2' => 'AU', 'alpha3' => 'AUS', 'num' =>'036', 'isd' => '61', "continent" => "Oceania", "en" => "Australia", "zh-CN" => "澳大利亚"],
        "AT" => ['alpha2' => 'AT', 'alpha3' => 'AUT', 'num' =>'040', 'isd' => '43', "continent" => "Europe", "en" => "Austria", "zh-CN" => "奥地利"],
        "AZ" => ['alpha2' => 'AZ', 'alpha3' => 'AZE', 'num' =>'031', 'isd' => '994', "continent" => "Asia", "en" => "Azerbaijan", "zh-CN" => "阿塞拜疆"],
        "BS" => ['alpha2' => 'BS', 'alpha3' => 'BHS', 'num' =>'044', 'isd' => '1242', "continent" => "North America", "en" => "Bahamas", "zh-CN" => "巴哈马"],
        "BH" => ['alpha2' => 'BH', 'alpha3' => 'BHR', 'num' =>'048', 'isd' => '973', "continent" => "Asia", "en" => "Bahrain", "zh-CN" => "巴林"],
        "BD" => ['alpha2' => 'BD', 'alpha3' => 'BGD', 'num' =>'050', 'isd' => '880', "continent" => "Asia", "en" => "Bangladesh", "zh-CN" => "孟加拉国"],
        "BB" => ['alpha2' => 'BB', 'alpha3' => 'BRB', 'num' =>'052', 'isd' => '1246', "continent" => "North America", "en" => "Barbados", "zh-CN" => "巴巴多斯"],
        "BY" => ['alpha2' => 'BY', 'alpha3' => 'BLR', 'num' =>'112', 'isd' => '375', "continent" => "Europe", "en" => "Belarus", "zh-CN" => "白俄罗斯"],
        "BE" => ['alpha2' => 'BE', 'alpha3' => 'BEL', 'num' =>'056', 'isd' => '32', "continent" => "Europe", "en" => "Belgium", "zh-CN" => "比利时"],
        "BZ" => ['alpha2' => 'BZ', 'alpha3' => 'BLZ', 'num' =>'084', 'isd' => '501', "continent" => "North America", "en" => "Belize", "zh-CN" => "伯利兹"],
        "BJ" => ['alpha2' => 'BJ', 'alpha3' => 'BEN', 'num' =>'204', 'isd' => '229', "continent" => "Africa", "en" => "Benin", "zh-CN" => "贝宁"],
        "BM" => ['alpha2' => 'BM', 'alpha3' => 'BMU', 'num' =>'060', 'isd' => '1441', "continent" => "North America", "en" => "Bermuda", "zh-CN" => "百慕大"],
        "BT" => ['alpha2' => 'BT', 'alpha3' => 'BTN', 'num' =>'064', 'isd' => '975', "continent" => "Asia", "en" => "Bhutan", "zh-CN" => "不丹"],
        "BO" => ['alpha2' => 'BO', 'alpha3' => 'BOL', 'num' =>'068', 'isd' => '591', "continent" => "South America", "en" => "Bolivia", "zh-CN" => "玻利维亚"],
        "BA" => ['alpha2' => 'BA', 'alpha3' => 'BIH', 'num' =>'070', 'isd' => '387', "continent" => "Europe", "en" => "Bosnia and Herzegovina", "zh-CN" => "波斯尼亚和黑塞哥维那"],
        "BW" => ['alpha2' => 'BW', 'alpha3' => 'BWA', 'num' =>'072', 'isd' => '267', "continent" => "Africa", "en" => "Botswana", "zh-CN" => "博茨瓦纳"],
        "BV" => ['alpha2' => 'BV', 'alpha3' => 'BVT', 'num' =>'074', 'isd' => '61', "continent" => "Antarctica", "en" => "Bouvet Island", "zh-CN" => "布维岛"],
        "BR" => ['alpha2' => 'BR', 'alpha3' => 'BRA', 'num' =>'076', 'isd' => '55', "continent" => "South America", "en" => "Brazil", "zh-CN" => "巴西"],
        "IO" => ['alpha2' => 'IO', 'alpha3' => 'IOT', 'num' =>'086', 'isd' => '246', "continent" => "Asia", "en" => "British Indian Ocean Territory", "zh-CN" => "英属印度洋领地"],
        "BN" => ['alpha2' => 'BN', 'alpha3' => 'BRN', 'num' =>'096', 'isd' => '672', "continent" => "Asia", "en" => "Brunei Darussalam", "zh-CN" => "文莱"],
        "BG" => ['alpha2' => 'BG', 'alpha3' => 'BGR', 'num' =>'100', 'isd' => '359', "continent" => "Europe", "en" => "Bulgaria", "zh-CN" => "保加利亚"],
        "BF" => ['alpha2' => 'BF', 'alpha3' => 'BFA', 'num' =>'854', 'isd' => '226', "continent" => "Africa", "en" => "Burkina Faso", "zh-CN" => "布基纳法索"],
        "BI" => ['alpha2' => 'BI', 'alpha3' => 'BDI', 'num' =>'108', 'isd' => '257', "continent" => "Africa", "en" => "Burundi", "zh-CN" => "布隆迪"],
        "KH" => ['alpha2' => 'KH', 'alpha3' => 'KHM', 'num' =>'116', 'isd' => '855', "continent" => "Asia", "en" => "Cambodia", "zh-CN" => "柬埔寨"],
        "CM" => ['alpha2' => 'CM', 'alpha3' => 'CMR', 'num' =>'120', 'isd' => '231', "continent" => "Africa", "en" => "Cameroon", "zh-CN" => "喀麦隆"],
        "CA" => ['alpha2' => 'CA', 'alpha3' => 'CAN', 'num' =>'124', 'isd' => '1', "continent" => "North America", "en" => "Canada", "zh-CN" => "加拿大"],
        "CV" => ['alpha2' => 'CV', 'alpha3' => 'CPV', 'num' =>'132', 'isd' => '238', "continent" => "Africa", "en" => "Cape Verde", "zh-CN" => "佛得角"],
        "KY" => ['alpha2' => 'KY', 'alpha3' => 'CYM', 'num' =>'136', 'isd' => '1345', "continent" => "North America", "en" => "Cayman Islands", "zh-CN" => "开曼群岛"],
        "CF" => ['alpha2' => 'CF', 'alpha3' => 'CAF', 'num' =>'140', 'isd' => '236', "continent" => "Africa", "en" => "Central African Republic", "zh-CN" => "中非共和国"],
        "TD" => ['alpha2' => 'TD', 'alpha3' => 'TCD', 'num' =>'148', 'isd' => '235', "continent" => "Africa", "en" => "Chad", "zh-CN" => "乍得"],
        "CL" => ['alpha2' => 'CL', 'alpha3' => 'CHL', 'num' =>'152', 'isd' => '56', "continent" => "South America", "en" => "Chile", "zh-CN" => "智利"],
        "CN" => ['alpha2' => 'CN', 'alpha3' => 'CHN', 'num' =>'156', 'isd' => '86', "continent" => "Asia", "en" => "China", "zh-CN" => "中国"],
        "CX" => ['alpha2' => 'CX', 'alpha3' => 'CXR', 'num' =>'162', 'isd' => '61', "continent" => "Asia", "en" => "Christmas Island", "zh-CN" => "圣诞岛"],
        "CC" => ['alpha2' => 'CC', 'alpha3' => 'CCK', 'num' =>'166', 'isd' => '891', "continent" => "Asia", "en" => "Cocos (Keeling) Islands", "zh-CN" => "科科斯（基林）群岛"],
        "CO" => ['alpha2' => 'CO', 'alpha3' => 'COL', 'num' =>'170', 'isd' => '57', "continent" => "South America", "en" => "Colombia", "zh-CN" => "哥伦比亚"],
        "KM" => ['alpha2' => 'KM', 'alpha3' => 'COM', 'num' =>'174', 'isd' => '269', "continent" => "Africa", "en" => "Comoros", "zh-CN" => "科摩罗"],
        "CG" => ['alpha2' => 'CG', 'alpha3' => 'COG', 'num' =>'178', 'isd' => '242', "continent" => "Africa", "en" => "Congo", "zh-CN" => "刚果（布）"],
        "CD" => ['alpha2' => 'CD', 'alpha3' => 'COD', 'num' =>'180', 'isd' => '243', "continent" => "Africa", "en" => "The Democratic Republic of The Congo", "zh-CN" => "刚果（金）"],
        "CK" => ['alpha2' => 'CK', 'alpha3' => 'COK', 'num' =>'184', 'isd' => '682', "continent" => "Oceania", "en" => "Cook Islands", "zh-CN" => "库克群岛"],
        "CR" => ['alpha2' => 'CR', 'alpha3' => 'CRI', 'num' =>'188', 'isd' => '506', "continent" => "North America", "en" => "Costa Rica", "zh-CN" => "哥斯达黎加"],
        "CI" => ['alpha2' => 'CI', 'alpha3' => 'CIV', 'num' =>'384', 'isd' => '225', "continent" => "Africa", "en" => "Cote D'ivoire", "zh-CN" => "科特迪瓦"],
        "HR" => ['alpha2' => 'HR', 'alpha3' => 'HRV', 'num' =>'191', 'isd' => '385', "continent" => "Europe", "en" => "Croatia", "zh-CN" => "克罗地亚"],
        "CU" => ['alpha2' => 'CU', 'alpha3' => 'CUB', 'num' =>'192', 'isd' => '53', "continent" => "North America", "en" => "Cuba", "zh-CN" => "古巴"],
        "CY" => ['alpha2' => 'CY', 'alpha3' => 'CYP', 'num' =>'196', 'isd' => '357', "continent" => "Asia", "en" => "Cyprus", "zh-CN" => "塞浦路斯"],
        "CZ" => ['alpha2' => 'CZ', 'alpha3' => 'CZE', 'num' =>'203', 'isd' => '420', "continent" => "Europe", "en" => "Czech Republic", "zh-CN" => "捷克共和国"],
        "DK" => ['alpha2' => 'DK', 'alpha3' => 'DNK', 'num' =>'208', 'isd' => '45', "continent" => "Europe", "en" => "Denmark", "zh-CN" => "丹麦"],
        "DJ" => ['alpha2' => 'DJ', 'alpha3' => 'DJI', 'num' =>'262', 'isd' => '253', "continent" => "Africa", "en" => "Djibouti", "zh-CN" => "吉布提"],
        "DM" => ['alpha2' => 'DM', 'alpha3' => 'DMA', 'num' =>'212', 'isd' => '1767', "continent" => "North America", "en" => "Dominica", "zh-CN" => "多米尼克"],
        "DO" => ['alpha2' => 'DO', 'alpha3' => 'DOM', 'num' =>'214', 'isd' => '1809', "continent" => "North America", "en" => "Dominican Republic", "zh-CN" => "多米尼加共和国"],
        "EC" => ['alpha2' => 'EC', 'alpha3' => 'ECU', 'num' =>'218', 'isd' => '593', "continent" => "South America", "en" => "Ecuador", "zh-CN" => "厄瓜多尔"],
        "EG" => ['alpha2' => 'EG', 'alpha3' => 'EGY', 'num' =>'818', 'isd' => '20', "continent" => "Africa", "en" => "Egypt", "zh-CN" => "埃及"],
        "SV" => ['alpha2' => 'SV', 'alpha3' => 'SLV', 'num' =>'222', 'isd' => '503', "continent" => "North America", "en" => "El Salvador", "zh-CN" => "萨尔瓦多"],
        "GQ" => ['alpha2' => 'GQ', 'alpha3' => 'GNQ', 'num' =>'226', 'isd' => '240', "continent" => "Africa", "en" => "Equatorial Guinea", "zh-CN" => "赤道几内亚"],
        "ER" => ['alpha2' => 'ER', 'alpha3' => 'ERI', 'num' =>'232', 'isd' => '291', "continent" => "Africa", "en" => "Eritrea", "zh-CN" => "厄立特里亚"],
        "EE" => ['alpha2' => 'EE', 'alpha3' => 'EST', 'num' =>'233', 'isd' => '372', "continent" => "Europe", "en" => "Estonia", "zh-CN" => "爱沙尼亚"],
        "ET" => ['alpha2' => 'ET', 'alpha3' => 'ETH', 'num' =>'231', 'isd' => '251', "continent" => "Africa", "en" => "Ethiopia", "zh-CN" => "埃塞俄比亚"],
        "FK" => ['alpha2' => 'FK', 'alpha3' => 'FLK', 'num' =>'238', 'isd' => '500', "continent" => "South America", "en" => "Falkland Islands (Malvinas)", "zh-CN" => "福克兰群岛"],
        "FO" => ['alpha2' => 'FO', 'alpha3' => 'FRO', 'num' =>'234', 'isd' => '298', "continent" => "Europe", "en" => "Faroe Islands", "zh-CN" => "法罗群岛"],
        "FJ" => ['alpha2' => 'FJ', 'alpha3' => 'FJI', 'num' =>'243', 'isd' => '679', "continent" => "Oceania", "en" => "Fiji", "zh-CN" => "斐济"],
        "FI" => ['alpha2' => 'FI', 'alpha3' => 'FIN', 'num' =>'246', 'isd' => '238', "continent" => "Europe", "en" => "Finland", "zh-CN" => "芬兰"],
        "FR" => ['alpha2' => 'FR', 'alpha3' => 'FRA', 'num' =>'250', 'isd' => '33', "continent" => "Europe", "en" => "France", "zh-CN" => "法国"],
        "GF" => ['alpha2' => 'GF', 'alpha3' => 'GUF', 'num' =>'254', 'isd' => '594', "continent" => "South America", "en" => "French Guiana", "zh-CN" => "法属圭亚那"],
        "PF" => ['alpha2' => 'PF', 'alpha3' => 'PYF', 'num' =>'258', 'isd' => '689', "continent" => "Oceania", "en" => "French Polynesia", "zh-CN" => "法属波利尼西亚"],
        "TF" => ['alpha2' => 'TF', 'alpha3' => 'ATF', 'num' =>'260', 'isd' => '262', "continent" => "Antarctica", "en" => "French Southern Territories", "zh-CN" => "法国南部地区"],
        "GA" => ['alpha2' => 'GA', 'alpha3' => 'GAB', 'num' =>'266', 'isd' => '241', "continent" => "Africa", "en" => "Gabon", "zh-CN" => "加蓬"],
        "GM" => ['alpha2' => 'GM', 'alpha3' => 'GMB', 'num' =>'270', 'isd' => '220', "continent" => "Africa", "en" => "Gambia", "zh-CN" => "冈比亚"],
        "GE" => ['alpha2' => 'GE', 'alpha3' => 'GEO', 'num' =>'268', 'isd' => '995', "continent" => "Asia", "en" => "Georgia", "zh-CN" => "格鲁吉亚"],
        "DE" => ['alpha2' => 'DE', 'alpha3' => 'DEU', 'num' =>'276', 'isd' => '49', "continent" => "Europe", "en" => "Germany", "zh-CN" => "德国"],
        "GH" => ['alpha2' => 'GH', 'alpha3' => 'GHA', 'num' =>'288', 'isd' => '233', "continent" => "Africa", "en" => "Ghana", "zh-CN" => "加纳"],
        "GI" => ['alpha2' => 'GI', 'alpha3' => 'GIB', 'num' =>'292', 'isd' => '350', "continent" => "Europe", "en" => "Gibraltar", "zh-CN" => "直布罗陀"],
        "GR" => ['alpha2' => 'GR', 'alpha3' => 'GRC', 'num' =>'300', 'isd' => '30', "continent" => "Europe", "en" => "Greece", "zh-CN" => "希腊"],
        "GL" => ['alpha2' => 'GL', 'alpha3' => 'GRL', 'num' =>'304', 'isd' => '299', "continent" => "North America", "en" => "Greenland", "zh-CN" => "格陵兰"],
        "GD" => ['alpha2' => 'GD', 'alpha3' => 'GRD', 'num' =>'308', 'isd' => '1473', "continent" => "North America", "en" => "Grenada", "zh-CN" => "格林纳达"],
        "GP" => ['alpha2' => 'GP', 'alpha3' => 'GLP', 'num' =>'312', 'isd' => '590', "continent" => "North America", "en" => "Guadeloupe", "zh-CN" => "瓜德罗普"],
        "GU" => ['alpha2' => 'GU', 'alpha3' => 'GUM', 'num' =>'316', 'isd' => '1871', "continent" => "Oceania", "en" => "Guam", "zh-CN" => "关岛"],
        "GT" => ['alpha2' => 'GT', 'alpha3' => 'GTM', 'num' =>'320', 'isd' => '502', "continent" => "North America", "en" => "Guatemala", "zh-CN" => "危地马拉"],
        "GG" => ['alpha2' => 'GG', 'alpha3' => 'GGY', 'num' =>'831', 'isd' => '44', "continent" => "Europe", "en" => "Guernsey", "zh-CN" => "根西岛"],
        "GN" => ['alpha2' => 'GN', 'alpha3' => 'GIN', 'num' =>'324', 'isd' => '224', "continent" => "Africa", "en" => "Guinea", "zh-CN" => "几内亚"],
        "GW" => ['alpha2' => 'GW', 'alpha3' => 'GNB', 'num' =>'624', 'isd' => '245', "continent" => "Africa", "en" => "Guinea-bissau", "zh-CN" => "几内亚比绍"],
        "GY" => ['alpha2' => 'GY', 'alpha3' => 'GUY', 'num' =>'328', 'isd' => '592', "continent" => "South America", "en" => "Guyana", "zh-CN" => "圭亚那"],
        "HT" => ['alpha2' => 'HT', 'alpha3' => 'HTI', 'num' =>'332', 'isd' => '509', "continent" => "North America", "en" => "Haiti", "zh-CN" => "海地"],
        "HM" => ['alpha2' => 'HM', 'alpha3' => 'HMD', 'num' =>'334', 'isd' => '672', "continent" => "Antarctica", "en" => "Heard Island and Mcdonald Islands", "zh-CN" => "赫德岛和麦克唐纳群岛"],
        "VA" => ['alpha2' => 'VA', 'alpha3' => 'VAT', 'num' =>'336', 'isd' => '379', "continent" => "Europe", "en" => "Holy See (Vatican City State)", "zh-CN" => "梵蒂冈"],
        "HN" => ['alpha2' => 'HN', 'alpha3' => 'HND', 'num' =>'340', 'isd' => '504', "continent" => "North America", "en" => "Honduras", "zh-CN" => "洪都拉斯"],
        "HK" => ['alpha2' => 'HK', 'alpha3' => 'HKG', 'num' =>'344', 'isd' => '852', "continent" => "Asia", "en" => "Hong Kong", "zh-CN" => "中国香港特别行政区"],
        "HU" => ['alpha2' => 'HU', 'alpha3' => 'HUN', 'num' =>'348', 'isd' => '36', "continent" => "Europe", "en" => "Hungary", "zh-CN" => "匈牙利"],
        "IS" => ['alpha2' => 'IS', 'alpha3' => 'ISL', 'num' =>'352', 'isd' => '354', "continent" => "Europe", "en" => "Iceland", "zh-CN" => "冰岛"],
        "IN" => ['alpha2' => 'IN', 'alpha3' => 'IND', 'num' =>'356', 'isd' => '91', "continent" => "Asia", "en" => "India", "zh-CN" => "印度"],
        "ID" => ['alpha2' => 'ID', 'alpha3' => 'IDN', 'num' =>'360', 'isd' => '62', "continent" => "Asia", "en" => "Indonesia", "zh-CN" => "印度尼西亚"],
        "IR" => ['alpha2' => 'IR', 'alpha3' => 'IRN', 'num' =>'364', 'isd' => '98', "continent" => "Asia", "en" => "Iran", "zh-CN" => "伊朗"],
        "IQ" => ['alpha2' => 'IQ', 'alpha3' => 'IRQ', 'num' =>'368', 'isd' => '964', "continent" => "Asia", "en" => "Iraq", "zh-CN" => "伊拉克"],
        "IE" => ['alpha2' => 'IE', 'alpha3' => 'IRL', 'num' =>'372', 'isd' => '353', "continent" => "Europe", "en" => "Ireland", "zh-CN" => "爱尔兰"],
        "IM" => ['alpha2' => 'IM', 'alpha3' => 'IMN', 'num' =>'833', 'isd' => '44', "continent" => "Europe", "en" => "Isle of Man", "zh-CN" => "曼岛"],
        "IL" => ['alpha2' => 'IL', 'alpha3' => 'ISR', 'num' =>'376', 'isd' => '972', "continent" => "Asia", "en" => "Israel", "zh-CN" => "以色列"],
        "IT" => ['alpha2' => 'IT', 'alpha3' => 'ITA', 'num' =>'380', 'isd' => '39', "continent" => "Europe", "en" => "Italy", "zh-CN" => "意大利"],
        "JM" => ['alpha2' => 'JM', 'alpha3' => 'JAM', 'num' =>'388', 'isd' => '1876', "continent" => "North America", "en" => "Jamaica", "zh-CN" => "牙买加"],
        "JP" => ['alpha2' => 'JP', 'alpha3' => 'JPN', 'num' =>'392', 'isd' => '81', "continent" => "Asia", "en" => "Japan", "zh-CN" => "日本"],
        "JE" => ['alpha2' => 'JE', 'alpha3' => 'JEY', 'num' =>'832', 'isd' => '44', "continent" => "Europe", "en" => "Jersey", "zh-CN" => "泽西岛"],
        "JO" => ['alpha2' => 'JO', 'alpha3' => 'JOR', 'num' =>'400', 'isd' => '962', "continent" => "Asia", "en" => "Jordan", "zh-CN" => "约旦"],
        "KZ" => ['alpha2' => 'KZ', 'alpha3' => 'KAZ', 'num' =>'398', 'isd' => '7', "continent" => "Asia", "en" => "Kazakhstan", "zh-CN" => "哈萨克斯坦"],
        "KE" => ['alpha2' => 'KE', 'alpha3' => 'KEN', 'num' =>'404', 'isd' => '254', "continent" => "Africa", "en" => "Kenya", "zh-CN" => "肯尼亚"],
        "KI" => ['alpha2' => 'KI', 'alpha3' => 'KIR', 'num' =>'296', 'isd' => '686', "continent" => "Oceania", "en" => "Kiribati", "zh-CN" => "基里巴斯"],
        "KP" => ['alpha2' => 'KP', 'alpha3' => 'PRK', 'num' =>'408', 'isd' => '850', "continent" => "Asia", "en" => "Democratic People's Republic of Korea", "zh-CN" => "朝鲜"],
        "KR" => ['alpha2' => 'KR', 'alpha3' => 'KOR', 'num' =>'410', 'isd' => '82', "continent" => "Asia", "en" => "Republic of Korea", "zh-CN" => "韩国"],
        "KW" => ['alpha2' => 'KW', 'alpha3' => 'KWT', 'num' =>'414', 'isd' => '965', "continent" => "Asia", "en" => "Kuwait", "zh-CN" => "科威特"],
        "KG" => ['alpha2' => 'KG', 'alpha3' => 'KGZ', 'num' =>'417', 'isd' => '996', "continent" => "Asia", "en" => "Kyrgyzstan", "zh-CN" => "吉尔吉斯斯坦"],
        "LA" => ['alpha2' => 'LA', 'alpha3' => 'LAO', 'num' =>'418', 'isd' => '856', "continent" => "Asia", "en" => "Lao People's Democratic Republic", "zh-CN" => "老挝"],
        "LV" => ['alpha2' => 'LV', 'alpha3' => 'LVA', 'num' =>'428', 'isd' => '371', "continent" => "Europe", "en" => "Latvia", "zh-CN" => "拉脱维亚"],
        "LB" => ['alpha2' => 'LB', 'alpha3' => 'LBN', 'num' =>'422', 'isd' => '961', "continent" => "Asia", "en" => "Lebanon", "zh-CN" => "黎巴嫩"],
        "LS" => ['alpha2' => 'LS', 'alpha3' => 'LSO', 'num' =>'426', 'isd' => '266', "continent" => "Africa", "en" => "Lesotho", "zh-CN" => "莱索托"],
        "LR" => ['alpha2' => 'LR', 'alpha3' => 'LBR', 'num' =>'430', 'isd' => '231', "continent" => "Africa", "en" => "Liberia", "zh-CN" => "利比里亚"],
        "LY" => ['alpha2' => 'LY', 'alpha3' => 'LBY', 'num' =>'434', 'isd' => '218', "continent" => "Africa", "en" => "Libya", "zh-CN" => "利比亚"],
        "LI" => ['alpha2' => 'LI', 'alpha3' => 'LIE', 'num' =>'438', 'isd' => '423', "continent" => "Europe", "en" => "Liechtenstein", "zh-CN" => "列支敦士登"],
        "LT" => ['alpha2' => 'LT', 'alpha3' => 'LTU', 'num' =>'440', 'isd' => '370', "continent" => "Europe", "en" => "Lithuania", "zh-CN" => "立陶宛"],
        "LU" => ['alpha2' => 'LU', 'alpha3' => 'LUX', 'num' =>'442', 'isd' => '352', "continent" => "Europe", "en" => "Luxembourg", "zh-CN" => "卢森堡"],
        "MO" => ['alpha2' => 'MO', 'alpha3' => 'MAC', 'num' =>'446', 'isd' => '853', "continent" => "Asia", "en" => "Macao", "zh-CN" => "中国澳门特别行政区"],
        "MK" => ['alpha2' => 'MK', 'alpha3' => 'MKD', 'num' =>'807', 'isd' => '389', "continent" => "Europe", "en" => "Macedonia", "zh-CN" => "马其顿"],
        "MG" => ['alpha2' => 'MG', 'alpha3' => 'MDG', 'num' =>'450', 'isd' => '261', "continent" => "Africa", "en" => "Madagascar", "zh-CN" => "马达加斯加"],
        "MW" => ['alpha2' => 'MW', 'alpha3' => 'MWI', 'num' =>'454', 'isd' => '265', "continent" => "Africa", "en" => "Malawi", "zh-CN" => "马拉维"],
        "MY" => ['alpha2' => 'MY', 'alpha3' => 'MYS', 'num' =>'458', 'isd' => '60', "continent" => "Asia", "en" => "Malaysia", "zh-CN" => "马来西亚"],
        "MV" => ['alpha2' => 'MV', 'alpha3' => 'MDV', 'num' =>'462', 'isd' => '960', "continent" => "Asia", "en" => "Maldives", "zh-CN" => "马尔代夫"],
        "ML" => ['alpha2' => 'ML', 'alpha3' => 'MLI', 'num' =>'466', 'isd' => '223', "continent" => "Africa", "en" => "Mali", "zh-CN" => "马里"],
        "MT" => ['alpha2' => 'MT', 'alpha3' => 'MLT', 'num' =>'470', 'isd' => '356', "continent" => "Europe", "en" => "Malta", "zh-CN" => "马耳他"],
        "MH" => ['alpha2' => 'MH', 'alpha3' => 'MHL', 'num' =>'584', 'isd' => '692', "continent" => "Oceania", "en" => "Marshall Islands", "zh-CN" => "马绍尔群岛"],
        "MQ" => ['alpha2' => 'MQ', 'alpha3' => 'MTQ', 'num' =>'474', 'isd' => '596', "continent" => "North America", "en" => "Martinique", "zh-CN" => "马提尼克"],
        "MR" => ['alpha2' => 'MR', 'alpha3' => 'MRT', 'num' =>'478', 'isd' => '222', "continent" => "Africa", "en" => "Mauritania", "zh-CN" => "毛里塔尼亚"],
        "MU" => ['alpha2' => 'MU', 'alpha3' => 'MUS', 'num' =>'480', 'isd' => '230', "continent" => "Africa", "en" => "Mauritius", "zh-CN" => "毛里求斯"],
        "YT" => ['alpha2' => 'YT', 'alpha3' => 'MYT', 'num' =>'175', 'isd' => '262', "continent" => "Africa", "en" => "Mayotte", "zh-CN" => "马约特"],
        "MX" => ['alpha2' => 'MX', 'alpha3' => 'MEX', 'num' =>'484', 'isd' => '52', "continent" => "North America", "en" => "Mexico", "zh-CN" => "墨西哥"],
        "FM" => ['alpha2' => 'FM', 'alpha3' => 'FSM', 'num' =>'583', 'isd' => '691', "continent" => "Oceania", "en" => "Micronesia", "zh-CN" => "密克罗尼西亚"],
        "MD" => ['alpha2' => 'MD', 'alpha3' => 'MDA', 'num' =>'498', 'isd' => '373', "continent" => "Europe", "en" => "Moldova", "zh-CN" => "摩尔多瓦"],
        "MC" => ['alpha2' => 'MC', 'alpha3' => 'MCO', 'num' =>'492', 'isd' => '377', "continent" => "Europe", "en" => "Monaco", "zh-CN" => "摩纳哥"],
        "MN" => ['alpha2' => 'MN', 'alpha3' => 'MNG', 'num' =>'496', 'isd' => '976', "continent" => "Asia", "en" => "Mongolia", "zh-CN" => "蒙古"],
        "ME" => ['alpha2' => 'ME', 'alpha3' => 'MNE', 'num' =>'499', 'isd' => '382', "continent" => "Europe", "en" => "Montenegro", "zh-CN" => "黑山"],
        "MS" => ['alpha2' => 'MS', 'alpha3' => 'MSR', 'num' =>'500', 'isd' => '1664', "continent" => "North America", "en" => "Montserrat", "zh-CN" => "蒙特塞拉特"],
        "MA" => ['alpha2' => 'MA', 'alpha3' => 'MAR', 'num' =>'504', 'isd' => '212', "continent" => "Africa", "en" => "Morocco", "zh-CN" => "摩洛哥"],
        "MZ" => ['alpha2' => 'MZ', 'alpha3' => 'MOZ', 'num' =>'508', 'isd' => '258', "continent" => "Africa", "en" => "Mozambique", "zh-CN" => "莫桑比克"],
        "MM" => ['alpha2' => 'MM', 'alpha3' => 'MMR', 'num' =>'104', 'isd' => '95', "continent" => "Asia", "en" => "Myanmar", "zh-CN" => "缅甸"],
        "NA" => ['alpha2' => 'NA', 'alpha3' => 'NAM', 'num' =>'516', 'isd' => '264', "continent" => "Africa", "en" => "Namibia", "zh-CN" => "纳米比亚"],
        "NR" => ['alpha2' => 'NR', 'alpha3' => 'NRU', 'num' =>'520', 'isd' => '674', "continent" => "Oceania", "en" => "Nauru", "zh-CN" => "瑙鲁"],
        "NP" => ['alpha2' => 'NP', 'alpha3' => 'NPL', 'num' =>'524', 'isd' => '977', "continent" => "Asia", "en" => "Nepal", "zh-CN" => "尼泊尔"],
        "NL" => ['alpha2' => 'NL', 'alpha3' => 'NLD', 'num' =>'528', 'isd' => '31', "continent" => "Europe", "en" => "Netherlands", "zh-CN" => "荷兰"],
        "AN" => ['alpha2' => 'AN', 'alpha3' => 'ANT', 'num' =>'530', 'isd' => '599', "continent" => "North America", "en" => "Netherlands Antilles", "zh-CN" => "荷属安的列斯群岛"],
        "NC" => ['alpha2' => 'NC', 'alpha3' => 'NCL', 'num' =>'540', 'isd' => '687', "continent" => "Oceania", "en" => "New Caledonia", "zh-CN" => "新喀里多尼亚"],
        "NZ" => ['alpha2' => 'NZ', 'alpha3' => 'NZL', 'num' =>'554', 'isd' => '64', "continent" => "Oceania", "en" => "New Zealand", "zh-CN" => "新西兰"],
        "NI" => ['alpha2' => 'NI', 'alpha3' => 'NIC', 'num' =>'558', 'isd' => '505', "continent" => "North America", "en" => "Nicaragua", "zh-CN" => "尼加拉瓜"],
        "NE" => ['alpha2' => 'NE', 'alpha3' => 'NER', 'num' =>'562', 'isd' => '227', "continent" => "Africa", "en" => "Niger", "zh-CN" => "尼日尔"],
        "NG" => ['alpha2' => 'NG', 'alpha3' => 'NGA', 'num' =>'566', 'isd' => '234', "continent" => "Africa", "en" => "Nigeria", "zh-CN" => "尼日利亚"],
        "NU" => ['alpha2' => 'NU', 'alpha3' => 'NIU', 'num' =>'570', 'isd' => '683', "continent" => "Oceania", "en" => "Niue", "zh-CN" => "纽埃"],
        "NF" => ['alpha2' => 'NF', 'alpha3' => 'NFK', 'num' =>'574', 'isd' => '672', "continent" => "Oceania", "en" => "Norfolk Island", "zh-CN" => "诺福克岛"],
        "MP" => ['alpha2' => 'MP', 'alpha3' => 'MNP', 'num' =>'580', 'isd' => '1670', "continent" => "Oceania", "en" => "Northern Mariana Islands", "zh-CN" => "北马里亚纳群岛"],
        "NO" => ['alpha2' => 'NO', 'alpha3' => 'NOR', 'num' =>'578', 'isd' => '47', "continent" => "Europe", "en" => "Norway", "zh-CN" => "挪威"],
        "OM" => ['alpha2' => 'OM', 'alpha3' => 'OMN', 'num' =>'512', 'isd' => '968', "continent" => "Asia", "en" => "Oman", "zh-CN" => "阿曼"],
        "PK" => ['alpha2' => 'PK', 'alpha3' => 'PAK', 'num' =>'586', 'isd' => '92', "continent" => "Asia", "en" => "Pakistan", "zh-CN" => "巴基斯坦"],
        "PW" => ['alpha2' => 'PW', 'alpha3' => 'PLW', 'num' =>'585', 'isd' => '680', "continent" => "Oceania", "en" => "Palau", "zh-CN" => "帕劳"],
        "PS" => ['alpha2' => 'PS', 'alpha3' => 'PSE', 'num' =>'275', 'isd' => '970', "continent" => "Asia", "en" => "Palestinia", "zh-CN" => "巴勒斯坦领土"],
        "PA" => ['alpha2' => 'PA', 'alpha3' => 'PAN', 'num' =>'591', 'isd' => '507', "continent" => "North America", "en" => "Panama", "zh-CN" => "巴拿马"],
        "PG" => ['alpha2' => 'PG', 'alpha3' => 'PNG', 'num' =>'598', 'isd' => '675', "continent" => "Oceania", "en" => "Papua New Guinea", "zh-CN" => "巴布亚新几内亚"],
        "PY" => ['alpha2' => 'PY', 'alpha3' => 'PRY', 'num' =>'600', 'isd' => '595', "continent" => "South America", "en" => "Paraguay", "zh-CN" => "巴拉圭"],
        "PE" => ['alpha2' => 'PE', 'alpha3' => 'PER', 'num' =>'604', 'isd' => '51', "continent" => "South America", "en" => "Peru", "zh-CN" => "秘鲁"],
        "PH" => ['alpha2' => 'PH', 'alpha3' => 'PHL', 'num' =>'608', 'isd' => '63', "continent" => "Asia", "en" => "Philippines", "zh-CN" => "菲律宾"],
        "PN" => ['alpha2' => 'PN', 'alpha3' => 'PCN', 'num' =>'612', 'isd' => '870', "continent" => "Oceania", "en" => "Pitcairn", "zh-CN" => "皮特凯恩群岛"],
        "PL" => ['alpha2' => 'PL', 'alpha3' => 'POL', 'num' =>'616', 'isd' => '48', "continent" => "Europe", "en" => "Poland", "zh-CN" => "波兰"],
        "PT" => ['alpha2' => 'PT', 'alpha3' => 'PRT', 'num' =>'620', 'isd' => '351', "continent" => "Europe", "en" => "Portugal", "zh-CN" => "葡萄牙"],
        "PR" => ['alpha2' => 'PR', 'alpha3' => 'PRI', 'num' =>'630', 'isd' => '1', "continent" => "North America", "en" => "Puerto Rico", "zh-CN" => "波多黎各"],
        "QA" => ['alpha2' => 'QA', 'alpha3' => 'QAT', 'num' =>'634', 'isd' => '974', "continent" => "Asia", "en" => "Qatar", "zh-CN" => "卡塔尔"],
        "RE" => ['alpha2' => 'RE', 'alpha3' => 'REU', 'num' =>'638', 'isd' => '262', "continent" => "Africa", "en" => "Reunion", "zh-CN" => "留尼汪"],
        "RO" => ['alpha2' => 'RO', 'alpha3' => 'ROU', 'num' =>'642', 'isd' => '40', "continent" => "Europe", "en" => "Romania", "zh-CN" => "罗马尼亚"],
        "RU" => ['alpha2' => 'RU', 'alpha3' => 'RUS', 'num' =>'643', 'isd' => '7', "continent" => "Europe", "en" => "Russian Federation", "zh-CN" => "俄罗斯"],
        "RW" => ['alpha2' => 'RW', 'alpha3' => 'RWA', 'num' =>'646', 'isd' => '250', "continent" => "Africa", "en" => "Rwanda", "zh-CN" => "卢旺达"],
        "SH" => ['alpha2' => 'SH', 'alpha3' => 'SHN', 'num' =>'654', 'isd' => '290', "continent" => "Africa", "en" => "Saint Helena", "zh-CN" => "圣赫勒拿"],
        "KN" => ['alpha2' => 'KN', 'alpha3' => 'KNA', 'num' =>'659', 'isd' => '1869', "continent" => "North America", "en" => "Saint Kitts and Nevis", "zh-CN" => "圣基茨和尼维斯"],
        "LC" => ['alpha2' => 'LC', 'alpha3' => 'LCA', 'num' =>'662', 'isd' => '1758', "continent" => "North America", "en" => "Saint Lucia", "zh-CN" => "圣卢西亚"],
        "PM" => ['alpha2' => 'PM', 'alpha3' => 'SPM', 'num' =>'666', 'isd' => '508', "continent" => "North America", "en" => "Saint Pierre and Miquelon", "zh-CN" => "圣皮埃尔和密克隆群岛"],
        "VC" => ['alpha2' => 'VC', 'alpha3' => 'VCT', 'num' =>'670', 'isd' => '1784', "continent" => "North America", "en" => "Saint Vincent and The Grenadines", "zh-CN" => "圣文森特和格林纳丁斯"],
        "WS" => ['alpha2' => 'WS', 'alpha3' => 'WSM', 'num' =>'882', 'isd' => '685', "continent" => "Oceania", "en" => "Samoa", "zh-CN" => "萨摩亚"],
        "SM" => ['alpha2' => 'SM', 'alpha3' => 'SMR', 'num' =>'674', 'isd' => '378', "continent" => "Europe", "en" => "San Marino", "zh-CN" => "圣马力诺"],
        "ST" => ['alpha2' => 'ST', 'alpha3' => 'STP', 'num' =>'678', 'isd' => '239', "continent" => "Africa", "en" => "Sao Tome and Principe", "zh-CN" => "圣多美和普林西比"],
        "SA" => ['alpha2' => 'SA', 'alpha3' => 'SAU', 'num' =>'682', 'isd' => '966', "continent" => "Asia", "en" => "Saudi Arabia", "zh-CN" => "沙特阿拉伯"],
        "SN" => ['alpha2' => 'SN', 'alpha3' => 'SEN', 'num' =>'686', 'isd' => '221', "continent" => "Africa", "en" => "Senegal", "zh-CN" => "塞内加尔"],
        "RS" => ['alpha2' => 'RS', 'alpha3' => 'SRB', 'num' =>'688', 'isd' => '381', "continent" => "Europe", "en" => "Serbia", "zh-CN" => "塞尔维亚"],
        "SC" => ['alpha2' => 'SC', 'alpha3' => 'SYC', 'num' =>'690', 'isd' => '248', "continent" => "Africa", "en" => "Seychelles", "zh-CN" => "塞舌尔"],
        "SL" => ['alpha2' => 'SL', 'alpha3' => 'SLE', 'num' =>'694', 'isd' => '232', "continent" => "Africa", "en" => "Sierra Leone", "zh-CN" => "塞拉利昂"],
        "SG" => ['alpha2' => 'SG', 'alpha3' => 'SGP', 'num' =>'702', 'isd' => '65', "continent" => "Asia", "en" => "Singapore", "zh-CN" => "新加坡"],
        "SK" => ['alpha2' => 'SK', 'alpha3' => 'SVK', 'num' =>'703', 'isd' => '421', "continent" => "Europe", "en" => "Slovakia", "zh-CN" => "斯洛伐克"],
        "SI" => ['alpha2' => 'SI', 'alpha3' => 'SVN', 'num' =>'705', 'isd' => '386', "continent" => "Europe", "en" => "Slovenia", "zh-CN" => "斯洛文尼亚"],
        "SB" => ['alpha2' => 'SB', 'alpha3' => 'SLB', 'num' =>'090', 'isd' => '677', "continent" => "Oceania", "en" => "Solomon Islands", "zh-CN" => "所罗门群岛"],
        "SO" => ['alpha2' => 'SO', 'alpha3' => 'SOM', 'num' =>'706', 'isd' => '252', "continent" => "Africa", "en" => "Somalia", "zh-CN" => "索马里"],
        "ZA" => ['alpha2' => 'ZA', 'alpha3' => 'ZAF', 'num' =>'729', 'isd' => '27', "continent" => "Africa", "en" => "South Africa", "zh-CN" => "南非"],
        "SS" => ['alpha2' => 'SS', 'alpha3' => 'SSD', 'num' =>'710', 'isd' => '211', "continent" => "Africa", "en" => "South Sudan", "zh-CN" => "南苏丹"],
        "GS" => ['alpha2' => 'GS', 'alpha3' => 'SGS', 'num' =>'239', 'isd' => '500', "continent" => "Antarctica", "en" => "South Georgia and The South Sandwich Islands", "zh-CN" => "南乔治亚岛和南桑威齐群岛"],
        "ES" => ['alpha2' => 'ES', 'alpha3' => 'ESP', 'num' =>'724', 'isd' => '34', "continent" => "Europe", "en" => "Spain", "zh-CN" => "西班牙"],
        "LK" => ['alpha2' => 'LK', 'alpha3' => 'LKA', 'num' =>'144', 'isd' => '94', "continent" => "Asia", "en" => "Sri Lanka", "zh-CN" => "斯里兰卡"],
        "SD" => ['alpha2' => 'SD', 'alpha3' => 'SDN', 'num' =>'736', 'isd' => '249', "continent" => "Africa", "en" => "Sudan", "zh-CN" => "苏丹"],
        "SR" => ['alpha2' => 'SR', 'alpha3' => 'SUR', 'num' =>'740', 'isd' => '597', "continent" => "South America", "en" => "Suriname", "zh-CN" => "苏里南"],
        "SJ" => ['alpha2' => 'SJ', 'alpha3' => 'SJM', 'num' =>'744', 'isd' => '47', "continent" => "Europe", "en" => "Svalbard and Jan Mayen", "zh-CN" => "斯瓦尔巴特和扬马延"],
        "SZ" => ['alpha2' => 'SZ', 'alpha3' => 'SWZ', 'num' =>'748', 'isd' => '268', "continent" => "Africa", "en" => "Swaziland", "zh-CN" => "斯威士兰"],
        "SE" => ['alpha2' => 'SE', 'alpha3' => 'SWE', 'num' =>'752', 'isd' => '46', "continent" => "Europe", "en" => "Sweden", "zh-CN" => "瑞典"],
        "CH" => ['alpha2' => 'CH', 'alpha3' => 'CHE', 'num' =>'756', 'isd' => '41', "continent" => "Europe", "en" => "Switzerland", "zh-CN" => "瑞士"],
        "SY" => ['alpha2' => 'SY', 'alpha3' => 'SYR', 'num' =>'760', 'isd' => '963', "continent" => "Asia", "en" => "Syrian Arab Republic", "zh-CN" => "叙利亚"],
        "TW" => ['alpha2' => 'TW', 'alpha3' => 'TWN', 'num' =>'158', 'isd' => '886', "continent" => "Asia", "en" => "Taiwan, Province of China", "zh-CN" => "台湾"],
        "TJ" => ['alpha2' => 'TJ', 'alpha3' => 'TJK', 'num' =>'762', 'isd' => '992', "continent" => "Asia", "en" => "Tajikistan", "zh-CN" => "塔吉克斯坦"],
        "TZ" => ['alpha2' => 'TZ', 'alpha3' => 'TZA', 'num' =>'834', 'isd' => '255', "continent" => "Africa", "en" => "Tanzania, United Republic of", "zh-CN" => "坦桑尼亚"],
        "TH" => ['alpha2' => 'TH', 'alpha3' => 'THA', 'num' =>'764', 'isd' => '66', "continent" => "Asia", "en" => "Thailand", "zh-CN" => "泰国"],
        "TL" => ['alpha2' => 'TL', 'alpha3' => 'TLS', 'num' =>'626', 'isd' => '670', "continent" => "Asia", "en" => "Timor-leste", "zh-CN" => "东帝汶"],
        "TG" => ['alpha2' => 'TG', 'alpha3' => 'TGO', 'num' =>'768', 'isd' => '228', "continent" => "Africa", "en" => "Togo", "zh-CN" => "多哥"],
        "TK" => ['alpha2' => 'TK', 'alpha3' => 'TKL', 'num' =>'772', 'isd' => '690', "continent" => "Oceania", "en" => "Tokelau", "zh-CN" => "托克劳"],
        "TO" => ['alpha2' => 'TO', 'alpha3' => 'TON', 'num' =>'776', 'isd' => '676', "continent" => "Oceania", "en" => "Tonga", "zh-CN" => "汤加"],
        "TT" => ['alpha2' => 'TT', 'alpha3' => 'TTO', 'num' =>'780', 'isd' => '1868', "continent" => "North America", "en" => "Trinidad and Tobago", "zh-CN" => "特立尼达和多巴哥"],
        "TN" => ['alpha2' => 'TN', 'alpha3' => 'TUN', 'num' =>'788', 'isd' => '216', "continent" => "Africa", "en" => "Tunisia", "zh-CN" => "突尼斯"],
        "TR" => ['alpha2' => 'TR', 'alpha3' => 'TUR', 'num' =>'792', 'isd' => '90', "continent" => "Asia", "en" => "Turkey", "zh-CN" => "土耳其"],
        "TM" => ['alpha2' => 'TM', 'alpha3' => 'TKM', 'num' =>'795', 'isd' => '993', "continent" => "Asia", "en" => "Turkmenistan", "zh-CN" => "土库曼斯坦"],
        "TC" => ['alpha2' => 'TC', 'alpha3' => 'TCA', 'num' =>'796', 'isd' => '1649', "continent" => "North America", "en" => "Turks and Caicos Islands", "zh-CN" => "特克斯和凯科斯群岛"],
        "TV" => ['alpha2' => 'TV', 'alpha3' => 'TUV', 'num' =>'798', 'isd' => '688', "continent" => "Oceania", "en" => "Tuvalu", "zh-CN" => "图瓦卢"],
        "UG" => ['alpha2' => 'UG', 'alpha3' => 'UGA', 'num' =>'800', 'isd' => '256', "continent" => "Africa", "en" => "Uganda", "zh-CN" => "乌干达"],
        "UA" => ['alpha2' => 'UA', 'alpha3' => 'UKR', 'num' =>'804', 'isd' => '380', "continent" => "Europe", "en" => "Ukraine", "zh-CN" => "乌克兰"],
        "AE" => ['alpha2' => 'AE', 'alpha3' => 'ARE', 'num' =>'784', 'isd' => '971', "continent" => "Asia", "en" => "United Arab Emirates", "zh-CN" => "阿拉伯联合酋长国"],
        "GB" => ['alpha2' => 'GB', 'alpha3' => 'GBR', 'num' =>'826', 'isd' => '44', "continent" => "Europe", "en" => "United Kingdom", "zh-CN" => "英国"],
        "US" => ['alpha2' => 'US', 'alpha3' => 'USA', 'num' =>'840', 'isd' => '1', "continent" => "North America", "en" => "United States", "zh-CN" => "美国"],
        "UM" => ['alpha2' => 'UM', 'alpha3' => 'UMI', 'num' =>'581', 'isd' => '1', "continent" => "Oceania", "en" => "United States Minor Outlying Islands", "zh-CN" => "美国小离岛"],
        "UY" => ['alpha2' => 'UY', 'alpha3' => 'URY', 'num' =>'858', 'isd' => '598', "continent" => "South America", "en" => "Uruguay", "zh-CN" => "乌拉圭"],
        "UZ" => ['alpha2' => 'UZ', 'alpha3' => 'UZB', 'num' =>'860', 'isd' => '998', "continent" => "Asia", "en" => "Uzbekistan", "zh-CN" => "乌兹别克斯坦"],
        "VU" => ['alpha2' => 'VU', 'alpha3' => 'VUT', 'num' =>'548', 'isd' => '678', "continent" => "Oceania", "en" => "Vanuatu", "zh-CN" => "瓦努阿图"],
        "VE" => ['alpha2' => 'VE', 'alpha3' => 'VEN', 'num' =>'862', 'isd' => '58', "continent" => "South America", "en" => "Venezuela", "zh-CN" => "委内瑞拉"],
        "VN" => ['alpha2' => 'VN', 'alpha3' => 'VNM', 'num' =>'704', 'isd' => '84', "continent" => "Asia", "en" => "Vietnam", "zh-CN" => "越南"],
        "VG" => ['alpha2' => 'VG', 'alpha3' => 'VGB', 'num' =>'092', 'isd' => '1284', "continent" => "North America", "en" => "Virgin Islands, British", "zh-CN" => "英属维京群岛"],
        "VI" => ['alpha2' => 'VI', 'alpha3' => 'VIR', 'num' =>'850', 'isd' => '1430', "continent" => "North America", "en" => "Virgin Islands, U.S.", "zh-CN" => "美属维京群岛"],
        "WF" => ['alpha2' => 'WF', 'alpha3' => 'WLF', 'num' =>'876', 'isd' => '681', "continent" => "Oceania", "en" => "Wallis and Futuna", "zh-CN" => "瓦利斯和富图纳"],
        "EH" => ['alpha2' => 'EH', 'alpha3' => 'ESH', 'num' =>'732', 'isd' => '212', "continent" => "Africa", "en" => "Western Sahara", "zh-CN" => "西撒哈拉"],
        "YE" => ['alpha2' => 'YE', 'alpha3' => 'YEM', 'num' =>'887', 'isd' => '967', "continent" => "Asia", "en" => "Yemen", "zh-CN" => "也门"],
        "ZM" => ['alpha2' => 'ZM', 'alpha3' => 'ZMB', 'num' =>'894', 'isd' => '260', "continent" => "Africa", "en" => "Zambia", "zh-CN" => "赞比亚"],
        "ZW" => ['alpha2' => 'ZW', 'alpha3' => 'ZWE', 'num' =>'716', 'isd' => '263', "continent" => "Africa", "en" => "Zimbabwe", "zh-CN" => "津巴布韦"],
    ];

    /**
     * @param array $alpha2s
     * @return array
     */
    public static function filter($alpha2s = [])
    {
        sort($alpha2s);
        $result = array_filter(static::$countries, function ($key) use ($alpha2s) {
            return in_array($key, $alpha2s);
        }, ARRAY_FILTER_USE_KEY);
        return $result;
    }

    /*
     * function get()
     * @param $key - key field for the array of countries, set it to null if you want array without named indices
     * @param $requestedField - name of the field to be fetched in value part of array
     * @returns array contained key=>value pairs of the requested key and field
     *
     */
    public static function get( $keyField = 'alpha2', $requestedField = 'name' )
    {
        $supportedFields = array( 'alpha2', 'alpha3', 'num', 'isd', 'name', 'continent' );
        //check if field to be used as array key is passed
        if( !in_array( $keyField, $supportedFields ) ){
            $keyField = null;
        }

        //check if the $requestedField is supported or not
        if( !in_array( $requestedField, $supportedFields ) ){
            $requestedField = 'name'; //return country name if invalid/unsupported field name is request
        }

        $result = array();
        //copy each requested field from the countries array
        foreach( self::$countries as $k => $country ){
            if( $keyField ){
                $result[ $country[ $keyField ] ] = $country[ $requestedField ];
            }else{
                $result[] = $country[ $requestedField ];
            }
        }
        return $result;
    }


    /*
     * function get2d() returns 2d array of countries
     * @param $key - key field for the array of countries, set it to null if you want array without named indices
     * @param $requestedFields - array of name of the fields to be fetched in value part of array
     * @returns array contained key=>value pairs of the requested key and field
     *
     */
    public static function get2d( $keyField = 'alpha2', $requestedFields = array( 'alpha2', 'alpha3', 'num', 'isd', 'name', 'continent' ) )
    {
        $supportedFields = array( 'alpha2', 'alpha3', 'num', 'isd', 'name', 'continent' );
        //check if field to be used as array key is passed
        if( !in_array( $keyField, $supportedFields ) ){
            $keyField = null;
        }

        //check if the $fields is an array or not
        if( is_array( $requestedFields ) ){
            //check if each field requested is supported or not, else unset it
            foreach( $requestedFields as $index => $field ){
                if( !in_array( $field, $supportedFields ) ){
                    unset( $requestedFields[$index] );
                }
            }
        }else{
            $requestedFields = array();
        }
        $result = array();
        //copy each requested field from the countries array
        foreach( self::$countries as $k => $country ){
            $tmp = array( );
            foreach( $requestedFields as $field ){
                $tmp[ $field ] = $country[ $field ];
            }
            if( $keyField ){
                $result[ $country[ $keyField ] ] = $tmp;
            }else{
                $result[] = $tmp;
            }
        }
        return $result;
    }

    /*
    * function getFromContinent()
    * @param $key - key field for the array of countries, set it to null if you want array without named indices
    * @param $requestedField - name of the field to be fetched in value part of array
    * @param $continent - name of continent to use as filter
    * @returns array contained key=>value pairs of the requested key and field
    * Works exactly as get() above
    * But takes an extra param to enable filtering by continent
    *
    */
    public static function getFromContinent( $keyField = 'alpha2', $requestedField = 'name', $continent=null )
    {
        $supportedFields = array( 'alpha2', 'alpha3', 'num', 'isd', 'name', 'continent' );
        $supportedContinents = array( 'Africa', 'Antarctica', 'Asia', 'Europe', 'North America', 'Oceania', 'South America' );

        //check if field to be used as array key is passed
        if( !in_array( $keyField, $supportedFields ) ){
            $keyField = null;
        }

        //check if field to be used as continent is passed
        if( !in_array( $continent, $supportedContinents ) ){
            $continent = null;
        }

        //check if the $requestedField is supported or not
        if( !in_array( $requestedField, $supportedFields ) ){
            $requestedField = 'name'; //return country name if invalid/unsupported field name is request
        }

        $result = array();
        //copy each requested field from the countries array
        foreach( self::$countries as $k => $country ){
            if( $keyField ){
                if ( $continent ) {
                    if ( $country['continent'] == $continent ) {
                        $result[ $country[ $keyField ] ] = $country[ $requestedField ];
                    }
                } else {
                    $result[ $country[ $keyField ] ] = $country[ $requestedField ];
                }
            } else {
                if ( $continent ) {
                    if ( $country['continent'] == $continent ) {
                        $result[] = $country[ $requestedField ];
                    }
                } else {
                    $result[] = $country[ $requestedField ];
                }
            }
        }
        return $result;
    }

    /**
     * @link http://en.wikipedia.org/wiki/ISO_4217
     * On date of 2015-01-10
     *
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function getCurrencies($key = null, $default = null)
    {
        $data = [
            'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
            'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL',
            'BSD', 'BTC', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF',
            'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP',
            'DZD', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
            'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK',
            'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP',
            'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD',
            'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD',
            'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR',
            'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR',
            'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR',
            'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD',
            'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS',
            'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD',
            'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD',
            'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL'
        ];
        return $key === null ? $data : \yii\helpers\ArrayHelper::getValue($data, $key, $default);
    }
}
