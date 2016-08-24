<?php

namespace PayumTW\Allpay;

use Detection\MobileDetect;
use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use PayumTW\Allpay\Constants\DeviceType;
use PayumTW\Allpay\Constants\EncryptType;
use PayumTW\Allpay\Constants\ExtraPaymentInfo;
use PayumTW\Allpay\Constants\InvoiceState;
use PayumTW\Allpay\Constants\PaymentMethod;
use PayumTW\Allpay\Constants\PaymentMethodItem;
use PayumTW\Allpay\Constants\UseRedeem;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $code = [
        '4000001'   => '不存在此交易單號',
        '4000003'   => '延後開立成功',
        '4000002'   => '呼叫開立發票API失敗',
        '4000005'   => '交易類型錯誤',
        '4000004'   => '開立發票成功',
        '4000006'   => '交易單號錯誤',
        '3000002'   => '查無發票中獎資料，請確認!',
        '3000003'   => '發送通知類型錯誤',
        '3000001'   => '發送對象錯誤',
        '1'         => '成功.',
        '3'         => '成功.',
        '1600002'   => '發票作廢原因須在20字以內',
        '1600003'   => '無發票號碼資料',
        '1600000'   => '發票號碼不可為空白',
        '1600001'   => '無發票作廢原因',
        '1600006'   => '作廢發票失敗(該發票已被作廢過)',
        '1600007'   => '作廢發票失敗(無法新增)',
        '1600004'   => '該發票已過可作廢日期',
        '1600005'   => '作廢發票失敗(該發票已被折讓過，無法直接作廢發票並請確認該發票所開立的折讓單是否全部已作廢)',
        '1600008'   => '作廢發票失敗(執行時期錯誤)',
        '1600009'   => '自訂編號不可為空白',
        '1600011'   => '作廢發票失敗(該發票上傳失敗，無法作廢發票)',
        '1600010'   => '作廢發票失敗(該發票已被註銷過，無法作廢發票)',
        '1500051'   => '更新字軌設定錯誤',
        '1500045'   => '開立發票失敗(無效的發票類別)',
        '1500047'   => '開立發票失敗(取得發票字軌錯誤)',
        '1500046'   => '開立發票失敗(自訂編號重覆，請重新設定)',
        '1500049'   => '開立發票失敗(資料不正確)',
        '1500048'   => '開立發票失敗(找不到發票字軌)',
        '1000004'   => '執行時期錯誤',
        '1000005'   => '自定錯誤訊息',
        '1000000'   => 'timestamp檢查錯誤',
        '1000001'   => '計算回傳檢核碼失敗',
        '1000002'   => '查詢開立發票失敗',
        '1000003'   => '查詢作廢發票失敗',
        '999'       => '取號重複',
        '1200060'   => '自訂編號超過30位數限制',
        '1200061'   => '客戶編號超過20位數限制',
        '1200062'   => '客戶姓名超過30位數限制',
        '1200063'   => '發票類別錯誤',
        '1200064'   => '無開立混合稅率發票的權限',
        '1200065'   => '無商品課稅別',
        '1200066'   => '商品課稅別代碼錯誤',
        '1200067'   => '開立混合稅率發票字軌類別限收銀機發票',
        '1200068'   => '無效的發票類別(混合稅率發票字軌類別限收銀機發票)',
        '1200069'   => '傳入的開立日期時間格式不正確',
        '1200042'   => '無效的自然人憑證',
        '1200043'   => '無效的手機條碼',
        '1200040'   => '已填寫統一編號，無法捐贈發票',
        '1200041'   => '已填寫統一編號，載具類別不可為會員或自然人憑證載具',
        '1200046'   => '開立發票失敗(自訂編號重覆，請重新設定)',
        '1200047'   => '開立發票失敗(查無可使用字軌發票)',
        '1200044'   => '手機條碼驗證失敗',
        '1200045'   => '開立發票失敗(無效的發票類別)',
        '1200048'   => '開立發票失敗(無法取得發票號碼)',
        '1200049'   => '開立發票失敗(執行時期錯誤)',
        '1200071'   => '二聯式發票不得輸入統一編號',
        '1200070'   => '傳入的開立日期時間僅能為前48小時以內',
        '1200073'   => '已選擇列印紙本發票，載具類別需空白',
        '1200072'   => '客戶地址超過100位數限制',
        '1200075'   => '捐贈的發票不可列印',
        '1200074'   => '商品內含稅設定格式錯誤',
        '1200077'   => '商品明細備註格式不正確',
        '1200076'   => '有填入統一編號的發票請務必列印',
        '1200079'   => '當線下廠商開立發票無載具且無統一編號時，必須列印。',
        '1200024'   => '客戶(買受人)電子信箱及手機號碼不可同時為空白',
        '1200025'   => '客戶(買受人)電子信箱格式錯誤',
        '1200026'   => '客戶(買受人)市內電話或手機號碼格式錯誤',
        '1200027'   => '請選擇課稅類別或課稅類別代碼錯誤',
        '1200020'   => '客戶編號只接受英、數字與下底線格式',
        '1200021'   => '列印發票時，客戶(買受人)名稱須有值',
        '1200022'   => '客戶(買受人)名稱僅能為中英數字格式',
        '1200023'   => '列印發票時，買受人地址須有值',
        '1200028'   => '發票金額錯誤',
        '1200029'   => '發票金額格式錯誤',
        '1200053'   => '無捐贈註記',
        '1200052'   => '無效的匯入單號',
        '1200051'   => '無匯入單號',
        '1200050'   => '欄位格式錯誤',
        '1200057'   => '載入發票檔失敗',
        '1200056'   => '檔案大小超過2MB',
        '1200055'   => '無內容的發票檔',
        '1200054'   => '無效的捐贈註記代碼',
        '1200059'   => '匯入單號重覆',
        '1200058'   => '遺漏發票基本資料(該列資料可能為商品明細)',
        '1200006'   => '商品數量格式錯誤',
        '1200007'   => '商品數量超過5位數限制',
        '1200004'   => '商品名稱字數過長',
        '1200005'   => '無商品數量',
        '1200002'   => '商品明細資料格式不正確',
        '1200003'   => '無商品名稱',
        '1200000'   => '查無公司資料',
        '1200001'   => '無通關方式資料或通關方式代碼錯誤',
        '1200008'   => '無商品單位',
        '1200009'   => '商品單位名稱字數過長',
        '1200035'   => '您已選擇捐贈發票，不能索取紙本發票',
        '1200034'   => '無效的愛心碼',
        '1200037'   => '請於「載具編號」填寫買受人之自然人憑證號碼',
        '1200036'   => '請選擇載具類別或載具類別代碼錯誤',
        '1200031'   => '驗證發票金額發現錯誤，與商品合計金額不符',
        '1200030'   => '發票金額超過限制金額長度12位數',
        '1200033'   => '您已選擇捐贈發票，需輸入愛心碼或選擇受捐單位',
        '1200032'   => '列印註記代碼錯誤',
        '1200039'   => '已填寫統一編號，只能選擇列印紙本發票',
        '1200038'   => '請於「載具編號」填寫買受人之手機條碼資料',
        '1200017'   => '自訂編號只接受英、數字與下底線格式',
        '1200016'   => '無自訂編號',
        '1200015'   => '商品合計金額超過12位數限制',
        '1200014'   => '商品合計金額格式錯誤',
        '1200013'   => '無商品合計金額',
        '1200012'   => '商品價格超過8位數限制',
        '1200011'   => '商品價格格式錯誤',
        '1200010'   => '無商品價格',
        '1200019'   => '無客戶編號',
        '1200018'   => '客戶統一編號格式不正確，請確認',
        '12000531'  => '無效的捐贈註記',
        '12000341'  => '愛心碼驗證失敗',
        '1800008'   => '開立延遲(或觸發)發票失敗(交易單號重覆，請重新設定)',
        '1800009'   => '開立延遲(或觸發)發票失敗(新增失敗)',
        '1800004'   => '無交易單號或長度大於30',
        '1800005'   => '無效的交易類別',
        '1800006'   => '無效的交易類別名稱',
        '1800007'   => '無通知URL',
        '1800000'   => '無效的延遲註記',
        '1800001'   => '無延遲天數或格式錯誤',
        '1800002'   => '若為延遲開立時，則延遲天數須介於1至15天內',
        '1800003'   => '若為觸發開立時，則延遲天數須介於0至15天內',
        '1800010'   => '開立{0}發票失敗(執行時期錯誤)',
        '100000481' => '無法刪除！您的帳戶目前有提領中的資料或有設定自動提領功能',
        '100000071' => '此居留證號已使用, 若先前註冊未完成, 請於15分鐘後重新註冊.',
        '100000331' => '請輸入有效的居留證字號',
        '2000043'   => '折讓單作廢原因須在20字以內',
        '2000042'   => '作廢發票號碼不能折讓',
        '2000041'   => '該折讓單已過可作廢日期',
        '2000040'   => 'MerchantID Error',
        '2000047'   => '折讓作廢失敗(發票已被註銷過，無法折讓作廢)',
        '2000046'   => '折讓發票失敗(該發票已被註銷過，無法折讓發票)',
        '2000045'   => '查無折讓單作廢資料，請確認!',
        '2000044'   => '客戶姓名超過20位數限制',
        '2000049'   => '折讓發票失敗(該發票上傳失敗，無法折讓發票)',
        '2000048'   => '折讓商品明細 - 商品課稅別代碼錯誤',
        '2000052'   => '因Facebook授權資訊不足，無法註冊歐付寶會員，請先至【Facebook】>設定【應用程式】>移除AllpayLogin，再嘗試註冊。',
        '2000050'   => '折讓單作廢失敗(該折讓單上傳失敗，無法作廢折讓單)',
        '2000051'   => '折讓商品明細 - 商品第n列單位長度超過限制',
        '2000025'   => '折讓商品明細 - 無商品第n列單位',
        '2000024'   => '折讓商品明細 - 商品第n列數量超過限制',
        '2000027'   => '折讓商品明細 - 商品第n列單價格式錯誤',
        '2000026'   => '折讓商品明細 - 無商品第n列單價錯誤',
        '2000021'   => '折讓商品明細 - 商品第n列數量錯誤',
        '2000020'   => '折讓商品明細 - 無商品第n列名稱',
        '2000023'   => '折讓商品明細 - 商品第n列數量不得為 0',
        '2000022'   => '折讓商品明細 - 商品第n列數量格式錯誤',
        '2000029'   => '折讓商品明細 - 商品第n列單價金額超過限制',
        '2000028'   => '折讓商品明細 - 商品第n列單價不得為 0',
        '2000034'   => '無足夠金額可以折讓，請確認',
        '2000035'   => '該發票可折讓的金額已經為 0 元，無法折讓，請確認!',
        '2000036'   => '查無公司資料',
        '2000037'   => '折讓單單號格式錯誤',
        '2000030'   => '折讓商品明細 - 商品第n列合計金額錯誤',
        '2000031'   => '折讓商品明細 - 商品第n列合計金額計算錯誤',
        '2000032'   => '折讓商品明細 - 商品第n列合計金額超過限制',
        '2000033'   => '驗證折讓發票金額發現錯誤，與折讓商品合計金額不符',
        '2000038'   => '無折讓單作廢原因',
        '2000039'   => '查無折讓單資料，請確認!',
        '2000007'   => '您選擇電子信箱為通知方式，但未填寫電子信箱!',
        '2000006'   => '您選擇手機簡訊為通知方式，但未填寫手機號碼!',
        '2000005'   => '請填寫電子信箱或手機號碼',
        '2000004'   => '無勾選通知方式',
        '2000003'   => '客戶(買受人)名稱僅能為中英數字格式',
        '2000002'   => '發票號碼格式錯誤',
        '2000001'   => 'timestamp檢查錯誤',
        '2000009'   => '無勾選通知方式',
        '2000008'   => '請填寫電子信箱及手機號碼',
        '2000016'   => '折讓商品明細 - 商品第1列單價錯誤',
        '2000017'   => '折讓商品明細 - 商品第1列合計金額錯誤',
        '2000014'   => '折讓商品明細 - 商品第1列數量錯誤',
        '2000015'   => '折讓商品明細 - 無商品第1列單位錯誤',
        '2000012'   => '折讓發票金額格式錯誤',
        '2000013'   => '折讓商品明細 - 無商品第1列名稱',
        '2000010'   => '電子信箱格式錯誤',
        '2000011'   => '手機號碼格式錯誤',
        '2000018'   => '無該筆發票資料!',
        '2000019'   => '商品明細資料錯誤',
        '10500001'  => 'MerchantTradeDate Is Null',
        '10500003'  => 'GoodsAmount Is Null',
        '10500002'  => 'LogisticsType Is Null',
        '10500005'  => 'ReceiverName Is Null',
        '10500004'  => 'SenderName Is Null',
        '10500007'  => 'SenderAddress Is Null',
        '10500006'  => 'SenderZipCode Is Null',
        '10500009'  => 'ReceiverAddress Is Null',
        '10500008'  => 'ReceiverZipCode Is Null',
        '10500010'  => 'ReceiverStoreID Is Null',
        '10500011'  => 'ReturnStoreID Is Null',
        '10500012'  => 'ServiceType is error.',
        '10500013'  => 'ReceiverPhone and ReceiverCellPhone Is Null',
        '10500014'  => 'SenderPhone and SenderCellPhone Is Null',
        '10500015'  => '出貨日期以及取貨門市代碼需擇一必填',
        '10500016'  => 'LogisticsID is null.',
        '10500017'  => 'GoodsName is null.',
        '10500018'  => 'CVSPaymentNo is null.',
        '10500019'  => 'CVSValidationNo is null.',
        '10500023'  => 'Distance is null.',
        '10500022'  => 'Temperature is null.',
        '10500021'  => '更新門市類型(StoreType)錯誤',
        '10500020'  => 'AllPayLogisticsID必須為整數。',
        '10500027'  => '無物流通知網址',
        '10500026'  => '無物流查詢訂單網址',
        '10500025'  => 'ScheduledDeliveryTime is null.',
        '10500024'  => 'Specification is null.',
        '10500029'  => 'GoodsAmount Error',
        '10500028'  => '物流回傳網址',
        '10500032'  => 'AllPayLogisticsID is null.',
        '10500033'  => 'RtnMerchantTradeNo Is Null',
        '10500030'  => 'LogisticsType Is Not Match.',
        '10500031'  => 'LogisticsSubType Is Not Match.',
        '10500036'  => '收件人姓名請設定為最多10字元(中文5個字, 英文10個字).',
        '10500037'  => 'LogisticsSubType Is Null.',
        '10500034'  => 'LogisticsC2CReplyURL Is Null',
        '10500035'  => '寄件人姓名請設定為最多10字元(中文5個字, 英文10個字).',
        '10500038'  => '商品名稱請設定為最多50字元(中文25個字, 英文50個字).',
        '10500039'  => '收件人手機號碼請輸入最少10字元.',
        '10400001'  => 'No HashKey',
        '10400002'  => 'MacValue Error',
        '10400003'  => 'TimeStamp Error',
        '9000001'   => '呼叫財政部API失敗',
        '10100400'  => '超過本次購物金可折抵上限，請重新輸入。',
        '10100050'  => 'Parameter Error.',
        '10100051'  => 'XML Parameter Error.',
        '10100052'  => 'Merchant ID Error.',
        '10100053'  => 'Merchant Status Error.',
        '10100054'  => 'Trading Number Repeated.',
        '10100055'  => 'Create Trade Fail.',
        '10100056'  => 'TradeNo Error.',
        '10100057'  => '找不到加密金鑰，請確認是否有申請開通此付款方式',
        '10100058'  => 'Pay Fail.',
        '10100059'  => 'Trading Number cannot Be Found.',
        '10100063'  => 'MerchantTradeNo Is Null.',
        '10100062'  => 'MerchantID And XMLData Content Are Not Match.',
        '10100061'  => 'ReplyURL Is Null.',
        '10100060'  => 'MerchantID Is Null.',
        '10100067'  => 'CharSet Is Null',
        '10100066'  => 'TradeDesc Is Null',
        '10100065'  => 'TradeAmount Format Error',
        '10100064'  => 'MerchantTradeDate Format Error',
        '10100069'  => 'ExpireDate Format Error',
        '10100068'  => 'Trade Amount Is Not Match',
        '10100072'  => 'Save CVS Trade Succeeded.',
        '10100073'  => 'Get CVS Code Succeeded.',
        '10100070'  => 'Trade Unconfirmed',
        '10100071'  => 'IdentifyNoNew Is Not Match.',
        '10100076'  => 'Merchant ID Error.',
        '10100077'  => 'Merchant Status Error.',
        '10100074'  => 'Update Trade OnGetCode Fail.',
        '10100075'  => 'Update Trade OnPaid Fail.',
        '10100078'  => 'Trading Number Repeated.',
        '10100079'  => 'Save CVS Trade Fail.',
        '10100001'  => 'IP Access Denied.',
        '10100002'  => 'Trade Error.',
        '10100010'  => 'Amount exceeds limit',
        '10100011'  => 'Amount exceeds account limit',
        '10100012'  => '帳戶金額不足',
        '10100013'  => '退貨金額超過扣款金額',
        '10100022'  => '使用非指定的銀行信用卡',
        '10100021'  => '信用卡退刷失敗',
        '10100020'  => '信用卡授權錯誤',
        '10100030'  => '未在儲值後30分鐘內進行放棄儲值',
        '10100031'  => '請至原儲值超商辦理放棄儲值',
        '10100089'  => 'Trading Number Repeated.',
        '10100088'  => 'Merchant Status Error.',
        '10100085'  => 'Pay Fail.',
        '10100084'  => 'Trade Amount Is Not Match',
        '10100087'  => 'Merchant ID Error.',
        '10100086'  => 'Trade Error.',
        '10100081'  => 'TradeNo Error.',
        '10100080'  => 'TradeID / TradeNo Fail.',
        '10100083'  => 'TradeNo Error.',
        '10100082'  => 'TradeNo Error.',
        '10100098'  => 'ExpireTime Format Error',
        '10100099'  => 'ExpireTime Error',
        '10100094'  => 'ItemCounts Is Null',
        '10100095'  => 'Email Is Null',
        '10100096'  => 'PhoneNo Is Null',
        '10100097'  => 'UserName Is Null',
        '10100090'  => 'Create Trade Fail.',
        '10100091'  => 'Create Trade Fail.',
        '10100092'  => 'Create Trade Fail.',
        '10100093'  => 'ItemNo Is Null',
        '10100140'  => 'Action Is Not Match.',
        '10100141'  => 'Verify Result Error.',
        '10100142'  => '已達交易限額',
        '10100104'  => 'Add PaymentCenter TradeDetail_CVS Repeat',
        '10100105'  => 'CVS TradeAmount just receive between 30 and 20000',
        '10100106'  => 'Parameter Error.',
        '10100107'  => 'Can not use CVS service.',
        '10100100'  => 'ServerReplyURL Is Null.',
        '10100101'  => 'ClientReplyURL Is Null.',
        '10100102'  => 'Can not use CVS service.',
        '10100103'  => 'Can not find any Barcode',
        '10100108'  => 'TradeType is not match',
        '10100109'  => 'TradeType is maintain',
        '10100126'  => '手機號碼停權中。',
        '10100127'  => '30分鐘內OTP失敗5次，手機號碼限制消費中1小時。',
        '10100124'  => 'Trade Error!',
        '10100125'  => 'Trade Error!',
        '10100122'  => 'Check CreditSecurity Fail',
        '10100123'  => 'Trade Error!',
        '10100120'  => 'Currency Error',
        '10100121'  => 'Discount Error',
        '10100128'  => '12小時內OTP失敗10次，手機號碼限制消費中24小時。',
        '10100129'  => 'IP停權中。',
        '10100115'  => 'IP In Block List',
        '10100114'  => 'CardNo In Block List',
        '10100117'  => 'InstallmentAmount Error',
        '10100116'  => 'Action Is Null.',
        '10100111'  => '商店信用卡交易額度已達上限，請重新下單選擇其他方式付款或聯繫商店處理。',
        '10100110'  => 'TradeAMT is Limit',
        '10100113'  => 'GwID Is Error.',
        '10100112'  => 'CardNo is existed.',
        '10100119'  => 'Redeem Error',
        '10100118'  => 'CreditInstallment Error',
        '10100137'  => '分期交易錯誤',
        '10100136'  => '商店無法使用此銀行卡片進行分期',
        '10100135'  => '同一訂單編號重覆申請授權',
        '10100133'  => 'IP限制消費中。',
        '10100132'  => '手機號碼限制消費中。',
        '10100131'  => '12小時內OTP失敗30次，IP限制消費24小時。',
        '10100130'  => '30分鐘內OTP失敗5次，IP限制消費1小時。',
        '10100139'  => '分期期數錯誤',
        '10100138'  => '分期交易錯誤',
        '10100261'  => '商店不存在',
        '10100260'  => '非台灣地區信用卡',
        '10100263'  => '金額參數錯誤',
        '10100262'  => '商店無法使用幕後授權',
        '10100265'  => '末三碼錯誤',
        '10100264'  => '到期日錯誤',
        '10100267'  => '商店交易上限額度超過',
        '10100266'  => '查無該發卡銀行',
        '10100269'  => '商店因為3D限制無法幕後授權',
        '10100268'  => '商店狀態系統停用',
        '10100270'  => '分期交易的商家,沒送分期期數',
        '10100271'  => '分期期數不適用於本商家',
        '10100272'  => '金額低於最低分期金額',
        '10100273'  => '超過一分鐘',
        '10100274'  => '銀行系統忙錄',
        '10100275'  => '銀行授權暫停',
        '10100276'  => '30秒內卡號保護',
        '10100243'  => 'TradeNo Is Null.',
        '10100242'  => 'CardID Is Null.',
        '10100241'  => 'TotalAmount Is Null.',
        '10100240'  => 'MerchantTradeDate Is Null.',
        '10100247'  => '3D交易認證錯誤',
        '10100246'  => '發卡行不符',
        '10100245'  => 'CardID cannot Be Found',
        '10100244'  => 'QueryDate Format Error',
        '10100249'  => '卡號錯誤',
        '10100248'  => '拒絕交易',
        '10100252'  => '額度不足',
        '10100253'  => '銀行回傳逾時放棄',
        '10100250'  => '特店對此卡別無分期交易之功能',
        '10100251'  => '卡片過期',
        '10100256'  => '被盜用卡',
        '10100257'  => '特店對此卡別無紅利交易之功能',
        '10100254'  => '請與發卡銀行聯絡',
        '10100255'  => '報失卡',
        '10100258'  => '無終端機代號',
        '10100259'  => '黑名單中的拒絕交交易卡號',
        '10100225'  => 'Frequency Error, PeriodType equal Y, Frequency equal 1',
        '10100224'  => 'Frequency Error, PeriodType equal M, Frequency between 1 and 12',
        '10100227'  => 'ExecTimes Error, PeriodType equal M, ExecTimes between 2 and 99',
        '10100226'  => 'ExecTimes Error, PeriodType equal D, ExecTimes between 2 and 999',
        '10100221'  => 'AlipayItemName And AlipayItemPrice Is Not Match.',
        '10100220'  => 'EndDate Format Error.',
        '10100223'  => 'Frequency Error, PeriodType equal D, Frequency between 1 and 365',
        '10100222'  => 'PeriodType Is Not Match.',
        '10100229'  => 'Periodic parameters incomplete.',
        '10100228'  => 'ExecTimes Error, PeriodType equal Y, ExecTimes between 2 and 9',
        '10100234'  => 'Reject Such Creditcard Trade',
        '10100235'  => 'RefundAmount Format Error',
        '10100236'  => 'CardNo Error',
        '10100237'  => 'TerminalID Error',
        '10100230'  => 'PeriodAmount Error',
        '10100231'  => 'DividendAmount Error',
        '10100232'  => 'DividendAmount and PeriodAmount is null',
        '10100233'  => 'TotalAmount and PeriodAmount are not match',
        '10100238'  => 'OTP Cellphone Error.',
        '10100239'  => 'Transaction Authorization Was Not Successful.',
        '10100207'  => 'AlipayItemCounts Is Error',
        '10100206'  => 'ChoosePayment Is Null',
        '10100205'  => 'ExpireDate between 1 and 60',
        '10100204'  => 'AlipayItemName Is Error',
        '10100203'  => 'ClientBackURL Is Null',
        '10100202'  => 'EventID Is Null',
        '10100201'  => 'MerchantMemberID Is Null',
        '10100200'  => '資料解密失敗，請確認資料是否正確方式加密',
        '10100209'  => 'AlipayItemName And AlipayItemCounts Is Not Match.',
        '10100208'  => 'AlipayItemPrice Is Error',
        '10100210'  => 'AliPayTotalAmount And TradeTotalAmount Are Not Match.',
        '10100211'  => 'ChoosePayment Is Not Match.',
        '10100218'  => 'TerminalID Is Null.',
        '10100219'  => 'StartDate Format Error.',
        '10100300'  => 'IgnorePayment Error.',
        '10001005'  => '新增會員Trans失敗',
        '10001004'  => '會員已存在-身份證',
        '10001007'  => '手機已經驗證過且Email已驗證過',
        '10001006'  => '手機已經驗證過且姓名相同',
        '10001001'  => '新增快速會員失敗',
        '10001003'  => '會員已存在-Email',
        '10001002'  => '手機或Mail重覆',
        '10001008'  => '身份證已經驗證過',
        '10000051'  => '操作錯誤!',
        '10000050'  => '更新失敗, 原安全提問題目或答案錯誤.',
        '10000053'  => '轉帳金額不得小於0.',
        '10000052'  => '轉帳金額不得小於等於0.',
        '10000055'  => '同一銀行與帳戶只能設定一種排程.',
        '10000054'  => '請點選正確的星期.',
        '10000057'  => '請勿與原交易密碼相同.',
        '10000056'  => '請勿與原登入密碼相同',
        '10000059'  => '必須選擇銀行分行.',
        '10000058'  => '請輸入正確的網址.',
        '10000040'  => '新增身份證驗證記錄失敗.',
        '10000041'  => '無此銀行代碼.',
        '10000042'  => '此統一編號已使用, 請重新輸入.',
        '10000043'  => '姓名請設定為最多60字元(中文30個字, 英文60個字).',
        '10000044'  => '請輸入大小寫英數字混合密碼.',
        '10000045'  => '銀行帳號最多設定五個, 最少設定一個.',
        '10000046'  => '姓名請設定為最小4字元(中文2個字, 英文4個字).',
        '10000047'  => '已超過可設定的銀行帳號數量.',
        '10000048'  => '無法刪除！您的預設帳戶必須設定一組銀行帳號',
        '10000049'  => '輸入錯誤',
        '10000073'  => '超商條碼，取號失敗，請重新操作一次。',
        '10000072'  => '超商代碼，取號失敗，請重新操作一次。',
        '10000071'  => '一天僅可以進行3次手機簡訊驗證。',
        '10000070'  => '手機簡訊驗證僅提供海外會員使用, 請重新選擇.',
        '10000077'  => '憑證簽章&驗章成功',
        '10000076'  => '憑證簽章&驗章失敗',
        '10000075'  => '此憑證檔案不符合您所申請的憑證資訊',
        '10000074'  => '虛擬帳號，取號失敗，請重新操作一次。',
        '10000079'  => '憑證檔案有誤，憑證簽章&驗章失敗',
        '10000078'  => '憑證金鑰密碼有誤，憑證簽章&驗章失敗',
        '10000062'  => '超商條碼資料有誤，無法郵寄，',
        '10000063'  => '超商條碼資料，即將準備郵寄。',
        '10000060'  => '姓名請輸入中文，不可包含英/數字、空白與特殊符號，如有問題請與客服聯繫。',
        '10000061'  => '轉帳金額不可小於16元',
        '10000066'  => '請輸入正確的海外手機格式',
        '10000067'  => '該身份證號碼已被使用或已驗證!',
        '10000064'  => '超商條碼資料存檔失敗，無法郵寄。',
        '10000065'  => '郵寄處理中！',
        '10000068'  => '該身份證號碼已被使用',
        '10000069'  => '因為您操作已逾時, 為了安全起見請重新註冊!',
        '10000015'  => '發生錯誤, 無法修改簡訊驗證資料.',
        '10000014'  => '手機號碼已使用過, 但未過手機號碼驗證.',
        '10000017'  => '簡訊驗證失敗.',
        '10000016'  => '發生錯誤, 無法新增簡訊驗證資料.',
        '10000011'  => '兩次登入密碼不一樣',
        '10000010'  => '程式發生無法預期的錯誤.',
        '10000013'  => '手機號碼已使用過, 並通過手機號碼驗證.',
        '10000012'  => '兩次交易密碼不一樣.',
        '10000019'  => '自訂問題需要填寫.',
        '10000018'  => '簡訊驗證已超過期限而失效.',
        '10000004'  => '兩次電子郵件信箱不一樣.',
        '10000005'  => '您輸入的Email已被註冊過，請重新輸入或與客服中心聯繫 02-26550115',
        '10000006'  => '此手機已使用, 請重新輸入.',
        '10000007'  => '此身份證字號已使用, 若先前註冊未完成, 請於15分鐘後重新註冊.',
        '10000001'  => '缺少必填資料.',
        '10000002'  => '更新失敗.',
        '10000003'  => '新增失敗.',
        '10000008'  => '使用者帳號已停用.',
        '10000009'  => '帳號或密碼輸入錯誤.',
        '10000037'  => '請輸入正確的日期.',
        '10000036'  => '請輸入有效的統一編號.',
        '10000035'  => '請輸入正確的電話格式, 0X-XXXXXX.',
        '10000034'  => '請輸入英數字混合密碼.',
        '10000033'  => '請輸入有效的身份證字號.',
        '10000032'  => '請輸入正確的Email.',
        '10000031'  => '請輸入正確的手機號碼格式, 09XXXXXXXX.',
        '10000030'  => '驗證碼錯誤，請再輸入一次.',
        '10000039'  => '請輸入正確的發證日期.',
        '10000038'  => '請輸入正確的銀行帳號.',
        '10000026'  => 'Email驗證失敗.',
        '10000027'  => '請輸入正確的手機號碼格式, 09XXXXXXXX.',
        '10000024'  => '資料填寫有誤，請重新填寫。',
        '10000025'  => '無此Email資訊或該信箱已通過驗證.',
        '10000022'  => 'Email或手機不可為空白.',
        '10000023'  => '資料請完整填寫.',
        '10000020'  => '新增Email驗證記錄失敗.',
        '10000021'  => 'Email驗證信傳送失敗.',
        '10000028'  => '請選擇要上傳的檔案.',
        '10000029'  => '新增統一編號驗證記錄失敗.',
        '10000099'  => 'OTP已逾期，無法使用OPT重送功能.',
        '10000098'  => 'OTP重送次數超過3次.',
        '10000095'  => 'admin會員與一般會員的特店編號不相同',
        '10000094'  => '會員非admin權限',
        '10000097'  => '驗證錯誤次數超過.',
        '10000096'  => '手機簡訊發送失敗.',
        '10000091'  => '請輸入正確的信用卡格式',
        '10000090'  => '動態密碼驗證成功',
        '10000093'  => '手機號碼格式有誤, Mobile Number is error.',
        '10000092'  => '請輸入正確的信用卡驗証碼格式',
        '10000088'  => '重覆申請動態密碼',
        '10000089'  => 'Can not find the member data.',
        '10000084'  => '登入資料與參數資料不相符合',
        '10000086'  => 'ESUNRealNameAuth Error',
        '10000087'  => 'ESUNRealNameAuth is Existed.',
        '10000080'  => 'Json資料解密失敗',
        '10000081'  => 'TimeStamp必須為整數',
        '10000082'  => 'TimeStamp已超過三分鐘',
        '10000083'  => '請確認登入後再使用此功能。',
        '10000150'  => '請勿輸入不雅或易造成消費者混淆之文字',
        '10000151'  => '出生日期不可未滿20歲',
        '10000172'  => '內政部驗證失敗',
        '10000173'  => '查詢會員儲值金額上限發生錯誤',
        '10000170'  => '請輸入正確的換證類別',
        '10000171'  => '發證地點輸入有誤',
        '10000176'  => '建立儲值帳戶失敗',
        '10000177'  => '新增帳戶儲值上限為0',
        '10000174'  => '取得會員現有儲值金額及七天內儲值消費金額失敗',
        '10000175'  => '收/付款方會員編號錯誤',
        '10000178'  => '再次檢查帳戶可儲值金額上限失敗',
        '10000179'  => '新增基本記錄有誤',
        '10000141'  => '錯誤的出生日期',
        '10000140'  => '此歐付寶會員已綁定該平台會員',
        '10000142'  => '找不到會員資料',
        '10000145'  => '商店名稱不可為空',
        '10000147'  => '商店名稱已有人使用，請重新命名。',
        '10000146'  => '商品名稱不可有禁用文字',
        '10000149'  => '出生日期不可為空',
        '10000148'  => '商店名稱更新失敗',
        '10000114'  => '符號僅接受「-」及「@」及空格(空格不可連續且頭尾不可輸入空格)',
        '10000115'  => '最小4字元(中文2個字, 英文4個字).',
        '10000116'  => '商店名稱最多40字元(中文20個字, 英文40個字).',
        '10000117'  => '商店名稱請設定為最小4字元(中文2個字, 英文4個字).',
        '10000110'  => '不可使用空格、全型英數 / 符號，且符號僅接受「-」和「@」',
        '10000113'  => '最多40字元(中文20個字, 英文40個字).',
        '10000118'  => '請輸入正確信用卡有效年月格式',
        '10000119'  => '信用卡過期',
        '10000163'  => 'AllPayMemberID Is Null.',
        '10000162'  => '該手機號碼因先前綁定流程中斷而導致失敗，請於5分鐘後重新綁定，謝謝!',
        '10000167'  => '信用卡實名帳戶或者銀行帳戶尚未驗證.',
        '10000166'  => '身分證尚未驗證.',
        '10000165'  => '手機尚未驗證.',
        '10000164'  => 'Email 尚未驗證.',
        '10000169'  => '錯誤的發證日期',
        '10000168'  => '請輸入正確的發證日期',
        '10000136'  => 'Account or CellPhone Is Null',
        '10000137'  => 'ID Is Null',
        '10000134'  => '無查詢綁定會員網址',
        '10000135'  => '無綁定會員回傳網址',
        '10000132'  => 'PlatformData Is Null',
        '10000133'  => '新增會員失敗.',
        '10000130'  => 'GID Is Null.',
        '10000131'  => 'Can not find Bind Data.',
        '10000138'  => '以ID進行反查結果不符合',
        '10000139'  => 'PlatformID or AppCode Is Error.',
        '10000105'  => '對外顯示名稱請設定為最小4字元(中文2個字, 英文4個字).',
        '10000104'  => '對外顯示名稱必須由中文、英文與數字組成, 不可有特殊符號.',
        '10000107'  => '網站名稱最多20字元(中文10個字, 英文20個字).',
        '10000106'  => '身份證字號與性別不相符, 請重新選擇.',
        '10000101'  => 'OTP每隔2分鐘內只能重送一次.',
        '10000100'  => 'OTP驗證錯誤次數超過3次.',
        '10000103'  => '對外顯示名稱請設定為最多20字元(中文10個字, 英文20個字).',
        '10000102'  => '訂單建立失敗，請重新購買。',
        '10000109'  => '網站名稱請設定為最小4字元(中文2個字, 英文4個字).',
        '10000108'  => '網站名稱必須由中文、英文與數字組成, 不可有特殊符號.',
        '10000127'  => '此平台會員已綁定過歐付寶會員',
        '10000126'  => 'Bind Fail.',
        '10000125'  => '建立收款失敗',
        '10000124'  => 'BindResultServerURL Is Null',
        '10000123'  => 'BindResultClientURL Is Null',
        '10000122'  => 'PlatformMemberNo Is Null',
        '10000121'  => 'PlatformID Is Null',
        '10000120'  => '無卡片末三碼或格式錯誤',
        '10000129'  => '此歐付寶會員，非平台商綁定會員',
        '10000128'  => '會員超過五次登入失敗',
        '10000190'  => '參數錯誤',
        '10000191'  => '為增加資安強度，4碼將改為6碼',
        '10000192'  => '輸入錯誤達五次，請重新設定',
        '10000193'  => '已1年未修改支付密碼，請重新設定',
        '10000189'  => 'RemoveResultClientURL Is Null',
        '10000188'  => '本服務暫不提供非台灣手機號碼進行綁定，請先前往歐付寶官網註冊會員後，再次使用本服務。',
        '10000185'  => '資料有誤，請再次確認資料是否正確。',
        '10000184'  => '內政部驗證時間過長，請您稍後再試。',
        '10000187'  => 'TransSeller Error',
        '10000186'  => '銀行帳戶名稱需與姓名或公司名稱一致，請確認是否正確。',
        '10000181'  => '國籍不可為空',
        '10000180'  => '取得內政部驗證碼失敗',
        '10000183'  => '發證日期不可為空',
        '10000182'  => '國籍輸入有誤',
        '10300007'  => 'Redeem format Error',
        '10300006'  => '匯率格式錯誤',
        '10300005'  => 'XSD File Name Is Null',
        '10300004'  => 'TimeStamp Error.',
        '10300003'  => 'Parameter Error.',
        '10300002'  => 'Verify Error.',
        '10300001'  => 'Login Fail.',
        '10300009'  => 'AccountID Is Null',
        '10300008'  => 'LoginBackUrl Is Null',
        '10300016'  => 'AccountID Is Not Mach',
        '10300017'  => '使用者不同意授權個資給廠商',
        '10300014'  => 'Token已失效',
        '10300015'  => 'OpenKey Is Not Match',
        '10300012'  => 'OpenCode Is Null',
        '10300013'  => 'Token Is Not Match',
        '10300010'  => 'Token Is Null',
        '10300011'  => 'OpenKey Is Null',
        '10300018'  => '找不到此OpenCode相關設定，請至廠商後台確認是否有設定參數值',
        '10200071'  => 'ShippingState Error.',
        '10200070'  => '帳戶餘額不足.',
        '10200073'  => 'CheckMacValue Error.',
        '10200072'  => 'EncodeChartset Error.',
        '10200075'  => 'PaymentType Error',
        '10200074'  => '找不到加密金鑰，請確認是否有申請開通此付款方式',
        '10200077'  => 'TradeDesc Error',
        '10200076'  => 'TotalAmount Error',
        '10200079'  => 'ReturnURL Error',
        '10200078'  => 'ItemName Error',
        '10200060'  => 'MerchantID And JsonData Content Are Not Match.',
        '10200061'  => 'ChargebackTotalAmount And ChargebackAmount Are Not Match.',
        '10200062'  => 'ChargebackQuantity OR ChargebackAmount Error.',
        '10200063'  => 'TotalQuantity is not equal (GuaranteeAmount + UsedAmount + RefundAmount)',
        '10200064'  => 'UserName Is Null.',
        '10200065'  => 'Phone Is Null.',
        '10200066'  => 'GuaranteeTotalAmount And GuaranteeAmount Are Not Match.',
        '10200067'  => '退款金額不可小於0.',
        '10200068'  => 'TotalAmount is not equal (GuaranteeAmount + UsedAmount + RefundAmount)',
        '10200069'  => '該訂單為信用卡分期付款，必須整筆退刷.',
        '10200053'  => 'ItemStatus Error.',
        '10200052'  => 'MerchantTradeNo Error.',
        '10200051'  => 'MerchantID Error.',
        '10200050'  => 'AllPayTradeID Error.',
        '10200057'  => 'DeliveryNotify Fail.',
        '10200056'  => 'OrderChange Fail.',
        '10200055'  => 'OrderChange Parameter Error.',
        '10200054'  => 'Update TradeStatus Fail.',
        '10200059'  => 'ShippingDate Error.',
        '10200058'  => 'Web Service Error',
        '10200042'  => 'Update SerialNo Status Error.',
        '10200043'  => 'Add PaymentCenter TradeDetail_CVS Repeat',
        '10200040'  => 'SerialNo Error',
        '10200041'  => 'Update ItemDetail Error.',
        '10200046'  => '訂單不可重覆選擇付款方式.',
        '10200047'  => 'Cant not find the trade data.',
        '10200044'  => 'CVS TradeAmount just receive between 30 and 20000',
        '10200045'  => 'Get Payment Code Fail',
        '10200048'  => 'Trade Amount Is Not Match.',
        '10200049'  => 'Add Coins Error.',
        '10200035'  => 'Add BarcodeByPost Fail',
        '10200034'  => 'BarcodeByPost is Existed.',
        '10200037'  => 'Add TradeDetail_CVS Error',
        '10200036'  => 'Add Payment TradeDetail_CVS Repeat',
        '10200031'  => 'MerchantTradeNo Must be Number or English Letter.',
        '10200030'  => 'Exchange Error.',
        '10200033'  => 'AllPayTradeNo Error',
        '10200032'  => 'Update TradeDetail CVS Fail',
        '10200039'  => 'TradeAmount Error',
        '10200038'  => 'MerchantTradeNo Error',
        '10200024'  => 'This trade had been finished.',
        '10200025'  => 'Item Status Error.',
        '10200026'  => 'This Item had been used.',
        '10200027'  => 'TradeNo Error.',
        '10200020'  => 'SNO / UPW Error.',
        '10200021'  => 'SerialNo LuanchNotify Fail.',
        '10200022'  => 'MerchantID Error.',
        '10200023'  => 'AllPayTradeID In TradeItemsDetail ID Error.',
        '10200028'  => 'SerialNo had been used.',
        '10200029'  => 'SerialNo Status Error.',
        '10200017'  => 'Generate Serial Number Fail.',
        '10200016'  => 'Call GW CVS API Fail.',
        '10200015'  => 'Update Trade Fail.',
        '10200014'  => 'PaymentType And XMLData Content Are Not Match',
        '10200013'  => 'PaymentType Is Null',
        '10200012'  => 'ItemNo is duplicate.',
        '10200011'  => 'TradeAmount and SubTotalAmt + Discount Is Not Match.',
        '10200010'  => 'SubTotalAmt Is Not Match.',
        '10200019'  => 'ItemNo Error.',
        '10200018'  => 'AllPayTradeID In TradeNo Error.',
        '10200006'  => 'SubTotalAmt Format Error.',
        '10200007'  => 'ItemURL Format Error.',
        '10200004'  => 'Quantity Format Error.',
        '10200005'  => 'Price Format Error.',
        '10200002'  => 'Trade has been updated before.',
        '10200003'  => 'Trade Status Error.',
        '10200001'  => 'Can not use trade service.',
        '10200008'  => 'ShippingDate Format Error.',
        '10200009'  => 'ConsiderHour Format Error.',
        '10200094'  => '請檢查是否於正式環境誤用測試環境之MerchantID',
        '10200093'  => '請輸入安全提問答案',
        '10200092'  => '請選擇安全提問問題',
        '10200091'  => 'CODStateNotifyURL is null.',
        '10200090'  => 'CheckMacValue is null.',
        '10200088'  => 'TopUp Error.',
        '10200089'  => 'IP Blocked',
        '10200086'  => 'ChargebackTotalAmount Error.',
        '10200087'  => 'TopUp Parameter Error.',
        '10200084'  => 'TimeStamp Is Expired.',
        '10200085'  => 'Use Error Query Spec.',
        '10200082'  => '安全提示答案輸入錯誤.',
        '10200083'  => 'TimeStamp Error.',
        '10200080'  => 'UseAllpayAddress Error',
        '10200081'  => 'InfoType Error.',
        '10200134'  => 'UserRefundAMT is required.',
        '10200135'  => '超商繳費截止時間格式錯誤',
        '10200136'  => '暫不提供【PlatformChargeFee 特約合作平台商手續費】參數服務，敬請見諒',
        '10200130'  => 'Phone No error.',
        '10200131'  => 'UpdatePlatformChargeFee error.',
        '10200132'  => 'Remark error.',
        '10200133'  => 'CaptureAMT is required.',
        '10200140'  => '退貨金額與訂單授權金額不一致',
        '10200144'  => '超過訂單繳費期限',
        '10200116'  => 'PlatformID Error',
        '10200114'  => 'ChargeBackTotalAmount And TradeTotalAmount Are Not Match',
        '10200115'  => 'Tenpay refund api error',
        '10200112'  => 'Allocated, can not refund',
        '10200113'  => 'Refunded, can not refund',
        '10200110'  => 'CreditCard trade refund use DoAction',
        '10200111'  => 'Buyer not login, can not refund',
        '10200118'  => 'AppCode Is Null',
        '10200119'  => '無查詢訂單網址',
        '10200125'  => '特約合作平台商手續費有值時，PlatformID不可空白',
        '10200124'  => '玉山儲值消費失敗',
        '10200127'  => 'Reduce Coins Error',
        '10200126'  => 'PlatformChargeFee Error',
        '10200121'  => 'ChooseSubPayment Is Not Match.',
        '10200120'  => '無付款通知網址',
        '10200123'  => 'OtpCode Is Null',
        '10200122'  => 'Merchant ID or AppCode Error.',
        '10200129'  => 'UserName error.',
        '10200128'  => '當付款方式為 Credit 時，則信用卡分期與紅利不可同時使用',
        '10200103'  => '信用卡分期期數超過有效範圍',
        '10200102'  => 'installment not support.',
        '10200101'  => 'Choose between installment or periodic',
    ];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] === false ?
            'https://payment.allpay.com.tw/Cashier/AioCheckOut/V2' :
            'https://payment-stage.allpay.com.tw/Cashier/AioCheckOut/V2';
    }

    /**
     * prepare.
     *
     * @param array $params
     * @param mixed $request
     *
     * @return array
     */
    public function preparePayment(array $params)
    {
        $supportedParams = [
            'MerchantID'        => $this->options['MerchantID'],
            'MerchantTradeNo'   => '',
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'PaymentType'       => 'aio',
            'TotalAmount'       => '',
            'TradeDesc'         => '',
            'ItemName'          => '',
            'ReturnURL'         => '',
            'ChoosePayment'     => PaymentMethod::ALL,
            'ClientBackURL'     => '',
            'ItemURL'           => '',
            'Remark'            => '',
            'ChooseSubPayment'  => PaymentMethodItem::None,
            'OrderResultURL'    => '',
            'NeedExtraPaidInfo' => ExtraPaymentInfo::No,
            'DeviceSource'      => $this->isMobile() ? DeviceType::Mobile : DeviceType::PC,
            'IgnorePayment'     => '',
            'PlatformID'        => '',
            'InvoiceMark'       => InvoiceState::No,
            'EncryptType'       => EncryptType::ENC_MD5,

            'Items'             => [],

            // ATM, Tenpay
            'ExpireDate'        => '',

            // CVS, BARCODE
            'Desc_1'            => '',
            'Desc_2'            => '',
            'Desc_3'            => '',
            'Desc_4'            => '',

            // Credit
            'CreditInstallment' => '',
            'InstallmentAmount' => '',
            'Redeem'            => '',
            'UseRedeem'         => UseRedeem::No,

            // Credit 定期定客
            'PeriodAmount'      => '',
            'PeriodType'        => '',
            'Frequency'         => '',
            'ExecTimes'         => '',
            'PeriodReturnURL'   => '',
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        if (empty($params['PlatformID']) === true) {
            unset($params['PlatformID']);
        }

        if (empty($params['ItemURL']) === true) {
            unset($params['ItemURL']);
        }

        if (count($params['Items']) > 0) {
            $itemName = '';
            foreach ($params['Items'] as $item) {
                $itemName .= vsprintf('#%s %d %s x %u', $item);
                if (array_key_exists('ItemURL', $params) === false && array_key_exists('URL', $item) === true) {
                    $params['ItemURL'] = $item['URL'];
                }
            }
            $params['ItemName'] = mb_substr($itemName, 1, 200);
        }
        unset($params['Items']);

        $params['CheckMacValue'] = $this->calculateHash($params);

        return $params;
    }

    /**
     * calculateHash.
     *
     * @param array $params
     *
     * @return string
     */
    protected function calculateHash($params)
    {
        if (isset($params['CheckMacValue']) === true) {
            unset($params['CheckMacValue']);
        }

        ksort($params, SORT_NATURAL | SORT_FLAG_CASE);
        $macValue = 'HashKey='.$this->options['HashKey'];
        foreach ($params as $key => $value) {
            $macValue .= '&'.$key.'='.$value;
        }
        $macValue .= '&HashIV='.$this->options['HashIV'];
        $macValue = urlencode($macValue);
        $macValue = strtolower($macValue);
        $macValue = strtr($macValue, [
            '%2d' => '-',
            '%5f' => '_',
            '%2e' => '.',
            '%21' => '!',
            '%2a' => '*',
            '%28' => '(',
            '%29' => ')',
        ]);

        return hash('md5', $macValue);
    }

    /**
     * Verify if the hash of the given parameter is correct.
     *
     * @param array $params
     *
     * @return bool
     */
    public function verifyHash(array $params)
    {
        if (empty($params['CheckMacValue'])) {
            return false;
        }

        $hash = $params['CheckMacValue'];
        unset($params['CheckMacValue']);

        return $hash === strtoupper($this->calculateHash($params));
    }

    /**
     * parseResult.
     *
     * @param mixed $params
     *
     * @return array
     */
    public function parseResult($params)
    {
        if ($this->verifyHash($params) === false) {
            $params['RtnCode'] = '10400002';
        }
        $params['statusReason'] = preg_replace('/(\.|。)$/', '', $this->getStatusReason($params['RtnCode']));

        return $params;
    }

    /**
     * getStatusReason.
     *
     * @param string $code
     *
     * @return string
     */
    protected function getStatusReason($code)
    {
        $statusReason = '拒絕交易';
        if (isset($this->code[$code]) === true) {
            $statusReason = $this->code[$code];
        }

        return $statusReason;
    }

    /**
     * isMobile.
     *
     * @return bool
     */
    protected function isMobile()
    {
        $detect = new MobileDetect();

        return ($detect->isMobile() === false && $detect->isTablet() === false) ? false : true;
    }
}
