-- Limite coerente per gli anni di esperienza dichiarabili da uno chef.
-- Migrazione non distruttiva: normalizza solo i valori fuori intervallo.

SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'chef'
    AND CONSTRAINT_NAME = 'chk_chef_anni_esperienza'
);

SET @drop_constraint = IF(
  @constraint_exists > 0,
  'ALTER TABLE chef DROP CONSTRAINT chk_chef_anni_esperienza',
  'SELECT 1'
);

PREPARE statement_drop_experience FROM @drop_constraint;
EXECUTE statement_drop_experience;
DEALLOCATE PREPARE statement_drop_experience;

UPDATE chef
SET anni_esperienza = 0
WHERE anni_esperienza < 0;

UPDATE chef
SET anni_esperienza = 60
WHERE anni_esperienza > 60;

ALTER TABLE chef
  ADD CONSTRAINT chk_chef_anni_esperienza
  CHECK (anni_esperienza BETWEEN 0 AND 60);
