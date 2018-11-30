-- vendor table creation.
-- 2005/7/20
-- ver 0.01

create table m_vendor(
  code char(5) not null,
  ckd char(1) not null,
  name varchar(40) not null default '',
  name_k varchar(50) not null,
  zip  char(7),
  address varchar(50),
  tel char(12),
  fax char(12),
  mail_address varchar(255),
  web_url varchar(255),
  isdcd char(6),
  update_userid char(6),
  updated timestamp,
  deleted timestamp
);

