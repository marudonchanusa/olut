
create table m_division(
  code   char(6) not null,
  name   varchar(128) not null default '',
  dlt 	 char(1),
  update_userid varchar(32),
  updated timestamp,
  deleted timestamp
);

