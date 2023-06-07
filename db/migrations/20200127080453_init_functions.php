<?php

use Phinx\Migration\AbstractMigration;

class InitFunctions extends AbstractMigration
{
  /**
   * Change Method.
   *
   * Write your reversible migrations using this method.
   *
   * More information on writing migrations is available here:
   * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
   *
   * The following commands can be used in this method and Phinx will
   * automatically reverse them when rolling back:
   *
   *    createTable
   *    renameTable
   *    addColumn
   *    addCustomColumn
   *    renameColumn
   *    addIndex
   *    addForeignKey
   *
   * Any other destructive changes will result in an error when trying to
   * rollback the migration.
   *
   * Remember to call "create()" or "update()" and NOT "save()" when working
   * with the Table class.
   */
  public function change()
  {
    $adapterType = $this->getAdapter()->getAdapterType();

    if ('mysql' === $adapterType) {
        $this->changeMysql();
    } elseif ('sqlsrv' === $adapterType) {
        $this->changeSqlServer();
    }
  }

    private function changeMysql()
    {
    }

  private function changeSqlServer()
  {
      $query = <<<QUERY
CREATE FUNCTION [dbo].[CONCAT](@str0 varchar(255) , @str1 varchar(255) = '123')
RETURNS varchar(255)
AS
BEGIN
    return @str0 + @str1;
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[FROM_UNIXTIME] (@timestamp bigint)
RETURNS char(19)
AS
BEGIN
  DECLARE @iyear  int,    @imonth int,     @iday int,     @ihours int,     @iminutes int,     @iseconds int;
  DECLARE @cyear char(4), @cmonth char(2), @cday char(2), @chours char(2), @cminutes char(2), @cseconds char(2);

  DECLARE @i int, @current_timestamp bigint, @temp bigint;

  DECLARE @returned_date char(19);

  SET @i = 1970;
  SET @current_timestamp = 0;
  SET @temp = 0;

  WHILE (@current_timestamp < @timestamp)
     BEGIN
       SET @temp = @current_timestamp;
       IF (@i%4 = 0)
           SET @current_timestamp = @current_timestamp + 366*24*60*60;
       ELSE
           SET @current_timestamp = @current_timestamp + 365*24*60*60;
        SET @i = @i + 1;
     END

  IF(@i = 1971)
     SET @temp = 0;

  SET @iyear = @i - 1;

  IF(@iyear%4 = 0)
     BEGIN
        SET @i = 1;
        WHILE (@current_timestamp < @timestamp )
           BEGIN
              IF(@i != 1)
                 SET @temp = @current_timestamp;
              IF (@i = 1)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 2)
                 SET @current_timestamp = @current_timestamp + 29*24*60*60;
              ELSE IF (@i = 3)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 4)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 5)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 6)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 7)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 8)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 9)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 10)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 11)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 12)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              SET @i = @i + 1;
     END
     END
  ELSE
     BEGIN
        SET @i = 1;
        WHILE (@current_timestamp < @timestamp )
           BEGIN
              IF(@i != 1)
                 SET @temp = @current_timestamp;
              IF (@i = 1)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 2)
                 SET @current_timestamp = @current_timestamp + 28*24*60*60;
              ELSE IF (@i = 3)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 4)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 5)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 6)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 7)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 8)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 9)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 10)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 11)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 12)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              SET @i = @i + 1;                             END
     END

  SET @imonth = @i;

  SET @iday              = (@timestamp - @temp)/(24*60*60);
  SET @ihours            = (@timestamp - @temp - @iday*24*60*60)/(60*60);
  SET @iminutes          = (@timestamp - @temp - @iday*24*60*60 - @ihours*60*60)/60;
  SET @iseconds          =  @timestamp - @temp - @iday*24*60*60 - @ihours*60*60 - @iminutes*60;

  SET @iday = @iday + 1;


  SET @cyear = STR(@iyear, 4, 0);
  IF (LEN(@imonth) = 2)
     SET @cmonth = STR(@imonth, 2, 0);
  ELSE
     SET @cmonth = '0' + STR(@imonth, 1, 0);

  IF (LEN(@iday) = 2)
     SET @cday = STR(@iday, 2, 0);
  ELSE
     SET @cday = '0' + STR(@iday, 1, 0);


  IF (LEN(@ihours) = 2)
     SET @chours = STR(@ihours, 2, 0);
  ELSE
     SET @chours = '0' + STR(@ihours, 1, 0);

  IF (LEN(@iminutes) = 2)
     SET @cminutes = STR(@iminutes, 2, 0);
  ELSE
     SET @cminutes = '0' + STR(@iminutes, 1, 0);

  IF (LEN(@iseconds) = 2)
     SET @cseconds = STR(@iseconds, 2, 0);
  ELSE
     SET @cseconds = '0' + STR(@iseconds, 1, 0);


  SET @returned_date = @cyear + '-' + @cmonth + '-' + @cday + ' ' + @chours + ':' + @cminutes + ':' + @cseconds;

  return @returned_date;
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[GREATEST] (@first_entry bigint, @second_entry bigint)
RETURNS bigint
AS
BEGIN
    DECLARE @return_value bigint;
    IF(@first_entry <= @second_entry)
        SET @return_value = @second_entry;
    ELSE
        SET @return_value = @first_entry;
    RETURN @return_value;
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[LEAST] (@first_entry bigint, @second_entry bigint)
RETURNS bigint
AS
BEGIN
    DECLARE @return_value bigint;
    IF(@first_entry <= @second_entry)
        SET @return_value = @first_entry;
    ELSE
        SET @return_value = @second_entry;
    RETURN @return_value;
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[PASSWORD] (@pass varchar(255))
RETURNS varchar(255)
AS
BEGIN
RETURN SUBSTRING([master].[dbo].fn_varbintohexstr(HashBytes('MD5', @pass)), 3, 32)
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[SHOW] (@table_name  varchar(30))
RETURNS varchar(30)
AS
BEGIN
   RETURN @table_name
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE FUNCTION [dbo].[UNIX_TIMESTAMP] (@indate datetime)
RETURNS BIGINT
AS
BEGIN
	DECLARE @diff BIGINT
	IF @indate < '19020101' OR @indate > '20380101'
	BEGIN
		SET @diff = DATEDIFF(minute, DATEADD(minute, DATEPART ( TZoffset , SYSDATETIMEOFFSET()), '1970-01-01 00:00:00' ), @indate)
		SET @diff = @diff * 60
	END
	ELSE
		SET @diff = DATEDIFF(second, DATEADD(minute, DATEPART ( TZoffset , SYSDATETIMEOFFSET()), '1970-01-01 00:00:00' ), @indate)
	RETURN @diff
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
create function [dbo].[ranker] (@evGroupId int, @group int, @rank int)
returns int
as
begin
return case when @evGroupId = @group then @rank+1 else 1 end
end
QUERY;
      $this->execute($query);

      $query = <<<QUERY
create FUNCTION [dbo].SUBSTRING_INDEX
(
@BaseString varchar(4000),
@caracter varchar(4000),
@pos int
) RETURNS varchar(4000)

AS
/* ****************************************************
Description:
EQuivalent a mysql substring_index
Created by Omar Rodriguez Tineo
**************************************************** */
BEGIN

/*
DECLARE @pos INT
Declare @BaseString varchar(255)
Declare @caracter varchar(255)
*/
Declare @indice tinyint
Declare @pos2 int
Declare @lastPos2 int
Declare @length int
Declare @result varchar(4000)

set @result = @BaseString
set @pos2= 1

set @length = LEN(@caracter)
if @pos <= 1
begin
   set @length = 0
end

set @lastPos2 = 1
set @indice = 0

while @indice < ABS(@pos)
begin

set @lastPos2 = @pos2
set @pos2 = CHARINDEX(@caracter,@BaseString,@pos2+1)

set @indice = @indice +1



if @indice = ABS(@pos)
begin
if (@pos2-1) <= 0
begin
set @result= substring(left(@BaseString,1), @lastPos2 + @length, 255)
break
end
else
begin
set @result= substring(left(@BaseString,(@pos2-1)), @lastPos2 + @length, 255)
break
end
end
else
continue

end

RETURN @result
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
create FUNCTION [dbo].LPAD
(
@str varchar(4000),
@length int,
@padstr varchar(1)
) RETURNS varchar(4000)
AS
BEGIN
RETURN replicate(@padstr, @length - len(@str)) + @str
END
QUERY;
      $this->execute($query);

      $query = <<<QUERY
create FUNCTION [dbo].RPAD
(
@str varchar(4000),
@length int,
@padstr varchar(1)
) RETURNS varchar(4000)
AS
BEGIN
RETURN @str + replicate(@padstr, @length - len(@str))
END
QUERY;
      $this->execute($query);


// CREATING OF GROUP_CONCAT

      $query = <<<QUERY
CREATE ASSEMBLY [GroupConcatProject]
AUTHORIZATION [dbo]
FROM 0x4D5A90000300000004000000FFFF0000B800000000000000400000000000000000000000000000000000000000000000000000000000000000000000800000000E1FBA0E00B409CD21B8014CCD21546869732070726F6772616D2063616E6E6F742062652072756E20696E20444F53206D6F64652E0D0D0A2400000000000000504500004C010300B4D7F5540000000000000000E00002210B010800000A000000060000000000007E290000002000000040000000004000002000000002000004000000000000000400000000000000008000000002000000000000030040850000100000100000000010000010000000000000100000000000000000000000242900005700000000400000D003000000000000000000000000000000000000006000000C00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000200000080000000000000000000000082000004800000000000000000000002E746578740000008409000000200000000A000000020000000000000000000000000000200000602E72737263000000D00300000040000000040000000C0000000000000000000000000000400000402E72656C6F6300000C000000006000000002000000100000000000000000000000000000400000420000000000000000000000000000000060290000000000004800000002000500102100001408000001000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000003202731000000A7D010000042A960F01281100000A2C012A027B010000040F01281200000A6F1300000A1F2C6F1400000A262A4E027B01000004037B010000046F1500000A262A00133004003D000000010000117E1600000A0A027B010000042C28027B010000046F1700000A16311A027B0100000416027B010000046F1700000A17596F1800000A0A06731900000A2A4A02036F1A00000A731B00000A7D010000042A4A03027B010000046F1C00000A6F1D00000A2A1E02281E00000A2A0042534A4201000100000000000C00000076322E302E35303732370000000005006C000000C8020000237E0000340300006C03000023537472696E677300000000A00600000800000023555300A8060000100000002347554944000000B80600005C01000023426C6F620000000000000002000001571702000900000000FA253300160000010000001700000002000000010000000700000005000000010000001E0000000D00000001000000010000000200000000000A0001000000000006003E0037000A006C005100060089007D000A00C400AF000600F300E90006000501E9000A00240151000600520140010600690140010600860140010600A50140010600BE0140010600D70140010600F201400106000D0240010600450226020600590240010600920272020600B20272020600E30237000A00F90251000A001A03510006003D03370000000000010000000000010001000120100021000000050001000100010097000A005020000000008600AA000E0001005D20000000008600CE00120001008320000000008600D900180002009820000000008600DF001E000300E12000000000E601000123000400F42000000000E601120129000500072100000000861818010E000600000001001E01000001003601000000000000000001003C01000001003E0102000900390018010E00410018014200490018014200510018014200590018014200610018014200690018014200710018014200790018014200810018014700890018014200910018014C00990018010E00A10018010E00A90018015100190018010E0021002103B90021002C03BD0019003603C10019003603C70019003603CD00B9004403D30019004A03D60019005503DA0021001801420029005E03BD0019001801420009005503BD00310012014200090018010E0024000B002F002E002B0002012E003300E4002E006B003B012E001B00FC002E002300FC002E001300E4002E003B0011012E004300FC002E005300FC002E006300320143007B00570064000B002F00E0000480000001000000A4153284000000000000D002000002000000000000000000000001002E0000000000020000000000000000000000010045000000000000000000003C4D6F64756C653E0047726F7570436F6E63617450726F6A6563742E646C6C0047524F55505F434F4E434154006D73636F726C69620053797374656D004F626A6563740053797374656D2E44617461004D6963726F736F66742E53716C5365727665722E536572766572004942696E61727953657269616C697A650053797374656D2E5465787400537472696E674275696C64657200696E7465726D656469617465526573756C7400496E69740053797374656D2E446174612E53716C54797065730053716C537472696E6700416363756D756C617465004D65726765005465726D696E6174650053797374656D2E494F0042696E61727952656164657200526561640042696E617279577269746572005772697465002E63746F720076616C75650053716C4661636574417474726962757465006F74686572007200770053797374656D2E5265666C656374696F6E00417373656D626C795469746C6541747472696275746500417373656D626C794465736372697074696F6E41747472696275746500417373656D626C79436F6E66696775726174696F6E41747472696275746500417373656D626C79436F6D70616E7941747472696275746500417373656D626C7950726F6475637441747472696275746500417373656D626C79436F7079726967687441747472696275746500417373656D626C7954726164656D61726B41747472696275746500417373656D626C7943756C747572654174747269627574650053797374656D2E52756E74696D652E496E7465726F70536572766963657300436F6D56697369626C6541747472696275746500417373656D626C7956657273696F6E4174747269627574650053797374656D2E52756E74696D652E436F6D70696C6572536572766963657300436F6D70696C6174696F6E52656C61786174696F6E734174747269627574650052756E74696D65436F6D7061746962696C6974794174747269627574650047726F7570436F6E63617450726F6A6563740053657269616C697A61626C654174747269627574650053716C55736572446566696E656441676772656761746541747472696275746500466F726D6174006765745F49734E756C6C006765745F56616C756500417070656E6400537472696E6700456D707479006765745F4C656E67746800546F537472696E670052656164537472696E670000000000032000000000001167674501B8FA46BBE8F0E4934611FA0008B77A5C561934E0890306120D03200001052001011111052001011208042000111105200101121505200101121912010001005408074D617853697A65FFFFFFFF042001010E042001010204200101080520010111596101000200000004005402124973496E76617269616E74546F4E756C6C73015402174973496E76617269616E74546F4475706C696361746573005402124973496E76617269616E74546F4F726465720054080B4D61784279746553697A65FFFFFFFF032000020320000E052001120D0E052001120D03052001120D1C02060E032000080520020E08080307010E1701001247726F7570436F6E63617450726F6A65637400000501000000000E0100094D6963726F736F667400002001001B436F7079726967687420C2A9204D6963726F736F6674203230313500000801000800000000001E01000100540216577261704E6F6E457863657074696F6E5468726F77730100004C29000000000000000000006E290000002000000000000000000000000000000000000000000000602900000000000000000000000000000000000000005F436F72446C6C4D61696E006D73636F7265652E646C6C0000000000FF25002040000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000100100000001800008000000000000000000000000000000100010000003000008000000000000000000000000000000100000000004800000058400000780300000000000000000000780334000000560053005F00560045005200530049004F004E005F0049004E0046004F0000000000BD04EFFE00000100000001003284A415000001003284A4153F000000000000000400000002000000000000000000000000000000440000000100560061007200460069006C00650049006E0066006F00000000002400040000005400720061006E0073006C006100740069006F006E00000000000000B004D8020000010053007400720069006E006700460069006C00650049006E0066006F000000B4020000010030003000300030003000340062003000000034000A00010043006F006D00700061006E0079004E0061006D006500000000004D006900630072006F0073006F00660074000000500013000100460069006C0065004400650073006300720069007000740069006F006E0000000000470072006F007500700043006F006E00630061007400500072006F006A006500630074000000000040000F000100460069006C006500560065007200730069006F006E000000000031002E0030002E0035003500340030002E00330033003800340032000000000050001700010049006E007400650072006E0061006C004E0061006D0065000000470072006F007500700043006F006E00630061007400500072006F006A006500630074002E0064006C006C00000000005C001B0001004C006500670061006C0043006F007000790072006900670068007400000043006F0070007900720069006700680074002000A90020004D006900630072006F0073006F006600740020003200300031003500000000005800170001004F0072006900670069006E0061006C00460069006C0065006E0061006D0065000000470072006F007500700043006F006E00630061007400500072006F006A006500630074002E0064006C006C0000000000480013000100500072006F0064007500630074004E0061006D00650000000000470072006F007500700043006F006E00630061007400500072006F006A006500630074000000000044000F000100500072006F006400750063007400560065007200730069006F006E00000031002E0030002E0035003500340030002E00330033003800340032000000000048000F00010041007300730065006D0062006C0079002000560065007200730069006F006E00000031002E0030002E0035003500340030002E003300330038003400320000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002000000C000000803900000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000
WITH PERMISSION_SET = SAFE
QUERY;
      $this->execute($query);

      $query = <<<QUERY
ALTER ASSEMBLY [GroupConcatProject]
ADD FILE FROM 0xEFBBBF7573696E672053797374656D3B0D0A7573696E672053797374656D2E446174613B0D0A7573696E67204D6963726F736F66742E53716C5365727665722E5365727665723B0D0A7573696E672053797374656D2E446174612E53716C54797065733B0D0A7573696E672053797374656D2E494F3B0D0A7573696E672053797374656D2E546578743B0D0A0D0A5B53657269616C697A61626C655D0D0A5B53716C55736572446566696E6564416767726567617465280D0A20202020466F726D61742E55736572446566696E65642C202F2F75736520636C722073657269616C697A6174696F6E20746F2073657269616C697A652074686520696E7465726D65646961746520726573756C740D0A202020204973496E76617269616E74546F4E756C6C73203D20747275652C202F2F6F7074696D697A65722070726F70657274790D0A202020204973496E76617269616E74546F4475706C696361746573203D2066616C73652C202F2F6F7074696D697A65722070726F70657274790D0A202020204973496E76617269616E74546F4F72646572203D2066616C73652C202F2F6F7074696D697A65722070726F70657274790D0A202020204D61784279746553697A65203D202D3129202F2F6D6178696D756D2073697A6520696E206279746573206F66207065727369737465642076616C75650D0A5D0D0A7075626C696320636C6173732047524F55505F434F4E434154203A204942696E61727953657269616C697A650D0A7B0D0A202020202F2F2F203C73756D6D6172793E0D0A202020202F2F2F20546865207661726961626C65207468617420686F6C64732074686520696E7465726D65646961746520726573756C74206F662074686520636F6E636174656E6174696F6E0D0A202020202F2F2F203C2F73756D6D6172793E0D0A202020207072697661746520537472696E674275696C64657220696E7465726D656469617465526573756C743B0D0A0D0A202020202F2F2F203C73756D6D6172793E0D0A202020202F2F2F20496E697469616C697A652074686520696E7465726E616C206461746120737472756374757265730D0A202020202F2F2F203C2F73756D6D6172793E0D0A202020207075626C696320766F696420496E697428290D0A202020207B0D0A2020202020202020746869732E696E7465726D656469617465526573756C74203D206E657720537472696E674275696C64657228293B0D0A202020207D0D0A0D0A202020202F2F2F203C73756D6D6172793E0D0A202020202F2F2F20416363756D756C61746520746865206E6578742076616C75652C206E6F74206966207468652076616C7565206973206E756C6C0D0A202020202F2F2F203C2F73756D6D6172793E0D0A202020202F2F2F203C706172616D206E616D653D2276616C7565223E3C2F706172616D3E0D0A202020207075626C696320766F696420416363756D756C617465285B53716C4661636574284D617853697A65203D202D31295D2053716C537472696E672076616C7565290D0A202020207B0D0A20202020202020206966202876616C75652E49734E756C6C290D0A20202020202020207B0D0A20202020202020202020202072657475726E3B0D0A20202020202020207D0D0A0D0A2020202020202020746869732E696E7465726D656469617465526573756C742E417070656E642876616C75652E56616C7565292E417070656E6428272C27293B0D0A202020207D0D0A0D0A202020202F2F2F203C73756D6D6172793E0D0A202020202F2F2F204D6572676520746865207061727469616C6C7920636F6D70757465642061676772656761746520776974682074686973206167677265676174652E0D0A202020202F2F2F203C2F73756D6D6172793E0D0A202020202F2F2F203C706172616D206E616D653D226F74686572223E3C2F706172616D3E0D0A202020207075626C696320766F6964204D657267652847524F55505F434F4E434154206F74686572290D0A202020207B0D0A2020202020202020746869732E696E7465726D656469617465526573756C742E417070656E64286F746865722E696E7465726D656469617465526573756C74293B0D0A20202020202020200D0A202020207D0D0A202020205B72657475726E3A2053716C4661636574284D617853697A65203D202D31295D0D0A202020200D0A202020202F2F2F203C73756D6D6172793E0D0A202020202F2F2F2043616C6C65642061742074686520656E64206F66206167677265676174696F6E2C20746F2072657475726E2074686520726573756C7473206F6620746865206167677265676174696F6E2E0D0A202020202F2F2F203C2F73756D6D6172793E0D0A202020202F2F2F203C72657475726E733E3C2F72657475726E733E0D0A202020207075626C69632053716C537472696E67205465726D696E61746528290D0A202020207B0D0A2020202020202020737472696E67206F7574707574203D20737472696E672E456D7074793B0D0A20202020202020202F2F64656C6574652074686520747261696C696E6720636F6D6D612C20696620616E790D0A202020202020202069662028746869732E696E7465726D656469617465526573756C7420213D206E756C6C0D0A202020202020202020202020262620746869732E696E7465726D656469617465526573756C742E4C656E677468203E2030290D0A20202020202020207B0D0A2020202020202020202020206F7574707574203D20746869732E696E7465726D656469617465526573756C742E546F537472696E6728302C20746869732E696E7465726D656469617465526573756C742E4C656E677468202D2031293B0D0A20202020202020207D0D0A0D0A202020202020202072657475726E206E65772053716C537472696E67286F7574707574293B0D0A202020207D0D0A0D0A202020207075626C696320766F696420526561642842696E6172795265616465722072290D0A202020207B0D0A2020202020202020696E7465726D656469617465526573756C74203D206E657720537472696E674275696C64657228722E52656164537472696E672829293B0D0A202020207D0D0A0D0A202020207075626C696320766F69642057726974652842696E6172795772697465722077290D0A202020207B0D0A2020202020202020772E577269746528746869732E696E7465726D656469617465526573756C742E546F537472696E672829293B0D0A202020207D0D0A7D
AS N'GroupConcat.cs'
QUERY;
      $this->execute($query);

      $query = <<<QUERY
ALTER ASSEMBLY [GroupConcatProject]
ADD FILE FROM 0xEFBBBF7573696E672053797374656D2E5265666C656374696F6E3B0D0A7573696E672053797374656D2E52756E74696D652E436F6D70696C657253657276696365733B0D0A7573696E672053797374656D2E52756E74696D652E496E7465726F7053657276696365733B0D0A7573696E672053797374656D2E446174612E53716C3B0D0A0D0A2F2F2047656E6572616C20496E666F726D6174696F6E2061626F757420616E20617373656D626C7920697320636F6E74726F6C6C6564207468726F7567682074686520666F6C6C6F77696E670D0A2F2F20736574206F6620617474726962757465732E204368616E6765207468657365206174747269627574652076616C75657320746F206D6F646966792074686520696E666F726D6174696F6E0D0A2F2F206173736F636961746564207769746820616E20617373656D626C792E0D0A5B617373656D626C793A20417373656D626C795469746C65282247726F7570436F6E63617450726F6A65637422295D0D0A5B617373656D626C793A20417373656D626C794465736372697074696F6E282222295D0D0A5B617373656D626C793A20417373656D626C79436F6E66696775726174696F6E282222295D0D0A5B617373656D626C793A20417373656D626C79436F6D70616E7928224D6963726F736F667422295D0D0A5B617373656D626C793A20417373656D626C7950726F64756374282247726F7570436F6E63617450726F6A65637422295D0D0A5B617373656D626C793A20417373656D626C79436F707972696768742822436F7079726967687420C2A9204D6963726F736F6674203230313522295D0D0A5B617373656D626C793A20417373656D626C7954726164656D61726B282222295D0D0A5B617373656D626C793A20417373656D626C7943756C74757265282222295D0D0A0D0A5B617373656D626C793A20436F6D56697369626C652866616C7365295D0D0A0D0A2F2F0D0A2F2F2056657273696F6E20696E666F726D6174696F6E20666F7220616E20617373656D626C7920636F6E7369737473206F662074686520666F6C6C6F77696E6720666F75722076616C7565733A0D0A2F2F0D0A2F2F2020202020204D616A6F722056657273696F6E0D0A2F2F2020202020204D696E6F722056657273696F6E0D0A2F2F2020202020204275696C64204E756D6265720D0A2F2F2020202020205265766973696F6E0D0A2F2F0D0A2F2F20596F752063616E207370656369667920616C6C207468652076616C756573206F7220796F752063616E2064656661756C7420746865205265766973696F6E20616E64204275696C64204E756D626572730D0A2F2F206279207573696E672074686520272A272061732073686F776E2062656C6F773A0D0A5B617373656D626C793A20417373656D626C7956657273696F6E2822312E302E2A22295D0D0A0D0A
AS N'Properties\AssemblyInfo.cs'
QUERY;
      $this->execute($query);

      $query = <<<QUERY
EXEC sys.sp_addextendedproperty @name=N'SqlAssemblyProjectRoot', @value=N'c:\Users\mike\Documents\Visual Studio 2010\Projects\GroupConcatProject\GroupConcatProject' , @level0type=N'ASSEMBLY',@level0name=N'GroupConcatProject'
QUERY;
      $this->execute($query);

      $query = <<<QUERY
CREATE AGGREGATE [dbo].[GROUP_CONCAT]
(@value [nvarchar](max))
RETURNS[nvarchar](max)
EXTERNAL NAME [GroupConcatProject].[GROUP_CONCAT]
QUERY;
      $this->execute($query);
  }
}
