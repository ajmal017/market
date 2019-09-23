ALTER TABLE exchange_code
	ADD COLUMN main BOOLEAN DEFAULT true;

UPDATE exchange_code SET main = NULL WHERE code IN ('ICUS', 'CBT', 'NYME', 'CMX');

ALTER TABLE exchange_code
	ADD CONSTRAINT use_null_for_secondary_exchange_codes CHECK (main != false),
	ADD UNIQUE (exchange_id, main);

CREATE VIEW instrument_exchange AS
	SELECT exchange_code.code, instrument.*
	FROM instrument
	JOIN exchange_code ON instrument.exchange_id = exchange_code.exchange_id
		AND exchange_code.main;
