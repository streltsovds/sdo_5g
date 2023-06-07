(AMERICAN_AMERICA.AL32UTF8 -> HKEY_LOCAl_Machine/Sqoftware/Oracle -> NLS_LENG)
SQLPLUS /NOLOG
CONN / AS SYSDBA
create user &1 identified by &1 default tablespace users temporary tablespace temp;
grant create session, create table, create procedure, create view, create sequence, create trigger, create function to gazprom;
alter user gazprom quota unlimited on users; 

CREATE OR REPLACE PUBLIC SYNONYM GROUP_CONCAT FOR WM_CONCAT;