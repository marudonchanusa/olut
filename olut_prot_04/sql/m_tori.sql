
create table m_tori(
  id char(5) not null,
  ckd char(1) not null,
  name varchar(40) not null default '',
  name_k varchar(50) not null,
  yubin  char(7),
  address1 varchar(50),
  address2 varchar(50),
  tel char(12),
  fax char(12),
 isdcd char(6),
  update_userid char(6),
  updated datetime,
  deleted datetime
) TYPE=MyISAM;
