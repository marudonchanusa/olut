alter table m_commodity add column depletion_flag char(1);
update m_commodity set depletion_flag='1' where code='50002' or code='60002' or code='70002' or code='75002' or code='29800' or code='39800' or code='49800' or code='89800';

update m_commodity set depletion_flag=0 where depletion_flag is null;


