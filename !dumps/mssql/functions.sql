/****** Object:  UserDefinedFunction [dbo].[CONCAT]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[CONCAT](@str0 varchar(255) , @str1 varchar(255) = '123')
RETURNS varchar(255)
AS
BEGIN
    return @str0 + @str1;
END
GO

/****** Object:  UserDefinedFunction [dbo].[FROM_UNIXTIME]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
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
GO
/****** Object:  Table [dbo].[news2]    Script Date: 04/20/2010 17:50:55 ******/

/****** Object:  UserDefinedFunction [dbo].[GREATEST]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
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
GO

/****** Object:  UserDefinedFunction [dbo].[LEAST]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
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
GO

/****** Object:  UserDefinedFunction [dbo].[PASSWORD]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[PASSWORD] (@pass varchar(255))
RETURNS varchar(255)
AS
BEGIN
RETURN SUBSTRING([master].[dbo].fn_varbintohexstr(HashBytes('MD5', @pass)), 3, 32)
END
GO

/****** Object:  UserDefinedFunction [dbo].[SHOW]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[SHOW] (@table_name  varchar(30))
RETURNS varchar(30)
AS
BEGIN
   RETURN @table_name
END
GO

/****** Object:  UserDefinedFunction [dbo].[UNIX_TIMESTAMP]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
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
GO



SET ANSI_NULLS ON
go
set quoted_identifier on
go
create function [dbo].[ranker] (@evGroupId int, @group int, @rank int)
returns int
as
begin
return case when @evGroupId = @group then @rank+1 else 1 end
end
go

/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
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
GO
/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
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
GO
/**/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
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
GO
/**/