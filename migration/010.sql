ALTER TABLE instrument
	DROP CONSTRAINT instrument_code_key,
	ADD CONSTRAINT instrument_exchange_symbol UNIQUE (exchange_id, symbol);
