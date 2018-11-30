
create table m_division(
  code   char(5) not null,
  name   varchar(50) not null default '',
  name_k varchar(50) not null,
  o_code   char(5),
  isdcd    char(6),
  updated timestamp,
  deleted timestamp
);

