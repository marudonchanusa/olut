
create table m_warehouse(
  code   char(2) not null,
  name   varchar(50) not null default '',
  name_k varchar(50) not null,
  updated timestamp,
  deleted timestamp
);

