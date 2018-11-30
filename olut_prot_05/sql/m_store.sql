-- “X•Üƒ}ƒXƒ^
-- 2005/7/20
-- ver 0.01

create table m_store(
	code   char(5) not null,
	ckd	char(1),
	scode	char(5),
	sckd	char(1),
	isdcd	char(15),
	name	varchar(50),
	name_k	varchar(60),
	zip	char(7),
	address	varchar(50),
	tel	char(12),
	fax 	char(12),
	print_flag	char(1),
	open_date	date,
	close_date	date,
	mail_address	varchar(255),
	web_url		varchar(255),
	update_userid	varchar(32),
	updated		timestamp,
	deleted		timestamp
);
