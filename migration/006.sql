ALTER TABLE instrument
	DROP CONSTRAINT instrument_name_key,
	DROP CONSTRAINT instrument_name_lower_key,
	ADD UNIQUE (name, exchange_id),
	ADD UNIQUE (name_lower, exchange_id);
