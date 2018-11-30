
create table m_item(
  code   char(5) not null,
  name   varchar(50) not null default '',
  shitei  char(1),
  ktancd     char(2),
  ktanimei varchar(10),
  ktanimei_k varchar(20),
  jtancd     char(2),
  jtanmei     varchar(20),
  jtanmei_k   varchar(20),
  jtansu      number(5),
  ltancd      char(2),
  ltanmei     varchar(10),
  ltanmei_k   varchar(20),
  ltansu      number(5),
  shicd       char(2),
  accd        char(4),
  itmcd       char(4),
  isdcd       char(7),
  prtkbn      char(1),
  gentanka    number(7,2)
  raitanka    number(7,2)
  jancd       char(20),
  note        text,
  notek_k     text,
  update_userid char(6),
  updated    timestamp,
  deleted    timestamp
);
