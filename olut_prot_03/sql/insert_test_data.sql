-- 単位マスターデータ挿入

insert into m_unit(code,name,name_k) values ('01','ケース','ケース');
insert into m_unit(code,name,name_k) values ('06','缶','カン');
insert into m_unit(code,name,name_k) values ('11','本','ホン');
insert into m_unit(code,name,name_k) values ('16','Kg',キログラム');
insert into m_unit(code,name,name_k) values ('21','袋,'フクロ');
insert into m_unit(code,name,name_k) values ('26','￡','ホンド');
insert into m_unit(code,name,name_k) values ('31','枚','マイ');
insert into m_unit(code,name,name_k) values ('36','食','ショク');
insert into m_unit(code,name,name_k) values ('51','冊','サツ');
insert into m_unit(code,name,name_k) values ('56','タース','タース');
insert into m_unit(code,name,name_k) values ('61','足','ソク');
insert into m_unit(code,name,name_k) values ('66','タン','タン');
insert into m_unit(code,name,name_k) values ('71','組','クミ');
insert into m_unit(code,name,name_k) values ('76','束','タバ');
insert into m_unit(code,name,name_k) values ('81','個','コ');
insert into m_unit(code,name,name_k) values ('86','箱','ハコ');
insert into m_unit(code,name,name_k) values ('91','台','ダイ');
insert into m_unit(code,name,name_k) values ('96','g','グラムノモノ');
insert into m_unit(code,name,name_k) values ('97','mm','ミリノモノ');
insert into m_unit(code,name,name_k) values ('98','*','ゼンブ');


-- 本部部門マスターデータ挿入

insert into m_shipment_section(code,name,name_k) values ('10','仮勘定','カンジョウ');
insert into m_shipment_section(code,name,name_k) values ('30','酒類','サケルイ');
insert into m_shipment_section(code,name,name_k) values ('40','鮮魚','センギョ');
insert into m_shipment_section(code,name,name_k) values ('45','ＴＯ','テイクアウト');
insert into m_shipment_section(code,name,name_k) values ('50','食肉','ショクニク');
insert into m_shipment_section(code,name,name_k) values ('60','洋調','ヨウチョウ');
insert into m_shipment_section(code,name,name_k) values ('70','中華','チュウカ');
insert into m_shipment_section(code,name,name_k) values ('75','製菓','セイカ');
insert into m_shipment_section(code,name,name_k) values ('80','用度品','ヨウドヒン');

-- 店舗内部門マスターデータ挿入

insert into m_store_division(code,name,name_k) values ('00','店','ミセ');
insert into m_store_division(code,name,name_k) values ('11','生ビール','ナマビール');
insert into m_store_division(code,name,name_k) values ('13','その他ビール','ソノタビール');
insert into m_store_division(code,name,name_k) values ('15','日本酒','ニホンシュ');
insert into m_store_division(code,name,name_k) values ('17','洋酒','洋酒');
insert into m_store_division(code,name,name_k) values ('21','洋調','ヨウチョウ');
insert into m_store_division(code,name,name_k) values ('22','和調','ワチョウ');
insert into m_store_division(code,name,name_k) values ('23','中華','チュウカ');
insert into m_store_division(code,name,name_k) values ('24','その他料理','その他料理');
insert into m_store_division(code,name,name_k) values ('31','喫茶','キッサ');
insert into m_store_division(code,name,name_k) values ('32','売店','バイテン');
insert into m_store_division(code,name,name_k) values ('33','煙草','タバコ');
insert into m_store_division(code,name,name_k) values ('34','有楽町給食','ユウラクチョウキュウショク');
insert into m_store_division(code,name,name_k) values ('35','本社給食','ホンシャキュウショク');
insert into m_store_division(code,name,name_k) values ('42','赤塚寮','アカツカリョウ');
insert into m_store_division(code,name,name_k) values ('43','清和寮','セイワリョウ');
insert into m_store_division(code,name,name_k) values ('44','武蔵野寮','ムサシノリョウ');
insert into m_store_division(code,name,name_k) values ('45','大久保寮','オオクボリョウ');
insert into m_store_division(code,name,name_k) values ('46','井の頭寮','イノカシラリョウ');
insert into m_store_division(code,name,name_k) values ('47','田端女子寮','タバタジョシリョウ');
insert into m_store_division(code,name,name_k) values ('51','業者譲渡','ギョウシャジョウト');
insert into m_store_division(code,name,name_k) values ('52','個人譲渡','コジンジョウト');
insert into m_store_division(code,name,name_k) values ('61','仮勘定','カリカンジョウ');
insert into m_store_division(code,name,name_k) values ('62','酒類','サケルイ');
insert into m_store_division(code,name,name_k) values ('63','鮮魚類','センギョルイ');
insert into m_store_division(code,name,name_k) values ('64','ＴＯ','テイクアウト');
insert into m_store_division(code,name,name_k) values ('65','CK食肉','CKショクニク');
insert into m_store_division(code,name,name_k) values ('66','CK洋調','CKキッサ');
insert into m_store_division(code,name,name_k) values ('67','CK中華','CKチュウカ');
insert into m_store_division(code,name,name_k) values ('68','CK製菓','CKセイカ');
insert into m_store_division(code,name,name_k) values ('81','用度','ヨウド');

-- 倉庫マスターデータ挿入

insert into m_warehouse(code,name,name_k) values ('01','資材部倉庫','シザイブソウコ');
insert into m_warehouse(code,name,name_k) values ('02','外口倉庫','ソトクチソウコ');
insert into m_warehouse(code,name,name_k) values ('88','名義変更','メイギヘンコウ');

-- 商品マスターデータ挿入

insert into m_commodity(code,name,name_k,dist_flag,unit_code,order_unit_code,current_unit_price) values('10000','あめ','アメ','0','01','01',98);
insert into m_commodity(code,name,name_k,dist_flag,unit_code,order_unit_code,current_unit_price) values('10001','あさつき','アサツキ','0','01','01',110);
insert into m_commodity(code,name,name_k,dist_flag,unit_code,order_unit_code,current_unit_price) values('10002','味の素','アジノモト','0','01','01',120);
insert into m_commodity(code,name,name_k,dist_flag,unit_code,order_unit_code,current_unit_price) values('10003','せんべい','センベイ','0','01','01',130);

-- 在庫データ挿入
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/10','0','10000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/10','0','20000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/10','0','30000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/10','0','40000','01','01','01',100,10,1000);

-- 入庫データ挿入
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','10000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','20000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','10000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','20000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','10000','01','01','01',100,10,1000);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/15','1','20000','01','01','01',100,10,1000);

-- 出庫データ挿入
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/3','5','10000','01','01','01',100,-1,100);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/20','5','20000','01','01','01',100,-1,100);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/20','5','10000','01','01','01',100,-1,100);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/20','5','20000','01','01','01',100,-1,100);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/30','5','10000','01','01','01',100,-2,200);
insert into t_main (act_date,act_flag,com_code,orig_code,store_sec_code,warehouse_code, amount, unit_price, total_price) values ('2005/7/30','5','20000','01','01','01',100,-1,100);
