ALTER TABLE exchange_code
	ADD COLUMN main BOOLEAN DEFAULT true;

UPDATE exchange_code SET main = NULL WHERE code IN ('ICUS', 'CBT', 'NYME', 'CMX');

ALTER TABLE exchange_code
	ADD CONSTRAINT use_null_for_secondary_exchange_codes CHECK (main != false),
	ADD UNIQUE (exchange_id, main);

ALTER TABLE instrument
	ADD COLUMN name_lower VARCHAR(64) CHECK (name_lower = lower(name)),
	DROP CONSTRAINT instrument_name_key,
	ADD UNIQUE (name_lower);

UPDATE instrument SET name_lower = lower(name) WHERE name IS NOT NULL;

ALTER TABLE contract
	ADD COLUMN description TEXT,
	ADD COLUMN refreshed_at DATE,
	ADD COLUMN from_date DATE,
	ADD COLUMN to_date DATE;

CREATE VIEW instrument_exchange AS
	SELECT exchange_code.code AS exchange_code, instrument.*
	FROM instrument
	JOIN exchange_code ON instrument.exchange_id = exchange_code.exchange_id
		AND exchange_code.main;
