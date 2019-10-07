ALTER TABLE trade_day
	ALTER COLUMN volume TYPE NUMERIC,
	DROP CONSTRAINT high_is_highest,
	DROP CONSTRAINT low_is_lowest;
