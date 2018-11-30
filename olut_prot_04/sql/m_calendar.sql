-- カレンダーマスター生成
-- 2005/9/1
-- ver 0.01

create table m_calendar
(
code char(5),
monthly_due_date date,
process_target date,
update_userid varchar(32),
updated timestamp);
