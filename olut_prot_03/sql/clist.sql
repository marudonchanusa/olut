select code,name from m_commodity where code not in (select com_code from t_main) order by code;
