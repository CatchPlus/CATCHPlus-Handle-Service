DELIMITER ;;
DROP PROCEDURE `createTokens`;;
CREATE PROCEDURE `createTokens`(
  IN inPoolName  VARCHAR(255) CHARACTER SET ASCII,
  IN inTokens INT UNSIGNED
)
BEGIN
  DECLARE varCounter INT UNSIGNED DEFAULT 0;
  DECLARE varPoolId INT UNSIGNED DEFAULT NULL;
  SELECT getPoolId(inPoolName) INTO varPoolId;
  START TRANSACTION;
    SET foreign_key_checks = 0;
    SET unique_checks = 0;
    WHILE varCounter < inTokens DO
      INSERT INTO `TokenValues` (
        `tokenValue`
      ) VALUES (
        varCounter
      );
      INSERT INTO `Tokens` (
        `tokenId`, `poolId`, `tokenType`, `tokenCreated`, `tokenLength`
      ) VALUES (
        LAST_INSERT_ID(), varPoolId, 'text/plain', UNIX_TIMESTAMP(), LENGTH(varCounter)
      );
      SET varCounter = varCounter + 1;
    END WHILE;
    SET unique_checks=1;
    SET foreign_key_checks=1;
  COMMIT;
END;;
DELIMITER ;


DELIMITER ;;
DROP FUNCTION `getPoolId`;;
CREATE DEFINER=`topos`@`localhost` FUNCTION `getPoolId`(
  inPoolName  VARCHAR(255) CHARACTER SET ASCII
)
RETURNS BIGINT UNSIGNED
DETERMINISTIC
BEGIN
  DECLARE varPoolId INT UNSIGNED DEFAULT NULL;
  INSERT IGNORE INTO `Pools` (`poolName`) VALUES (inPoolName);
  SELECT LAST_INSERT_ID() INTO varPoolId;
  IF varPoolId = 0 THEN
    SELECT `poolId` INTO varPoolId FROM `Pools` WHERE `poolName` = inPoolName;
  END IF;
  RETURN varPoolId;
END;;
DELIMITER ;
