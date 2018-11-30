-- 年間入出庫ファイルから落としたデータ。
-- オフコンからのデータのインポートに使うが、実際のシステム稼動時には不要になる。

create table m_annual_inventory
(  com_code char(9),
   dt date,
   amount  numeric(11,2),
   price numeric(11)
);