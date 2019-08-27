TRUNCATE TABLE cot;

ALTER TABLE cot
	DROP COLUMN managed_long,
	DROP COLUMN managed_short,
	DROP COLUMN other_long,
	DROP COLUMN other_short,
	ADD COLUMN swap_spread INTEGER NOT NULL CHECK (swap_spread >= 0),
	ADD COLUMN managed_long INTEGER NOT NULL CHECK (managed_long >= 0),
	ADD COLUMN managed_short INTEGER NOT NULL CHECK (managed_short <= 0),
	ADD COLUMN managed_spread INTEGER NOT NULL CHECK (managed_spread >= 0),
	ADD COLUMN other_long INTEGER NOT NULL CHECK (other_long >= 0),
	ADD COLUMN other_short INTEGER NOT NULL CHECK (other_short <= 0),
	ADD COLUMN other_spread INTEGER NOT NULL CHECK (other_spread >= 0),
	ADD COLUMN nonreportable_long INTEGER NOT NULL CHECK (nonreportable_long >= 0),
	ADD COLUMN nonreportable_short INTEGER NOT NULL CHECK (nonreportable_short <= 0);

INSERT INTO cot (instrument_id, date, hedgers_long, hedgers_short, swap_long, swap_short, swap_spread, managed_long, managed_short, managed_spread, other_long, other_short, other_spread, nonreportable_long, nonreportable_short) VALUES
	(1, '2019-08-13', 124633, -147992, 14170, -1751, 16050, 37851, -42241, 36913, 16034, -4555, 31564, 15210, -11358),
	(2, '2019-08-13', 678737, -1071204, 205449, -30608, 62644, 210926, -166413, 267645, 142549, -49477, 440564, 343406, -263364);

CREATE VIEW cot_legacy AS
	SELECT instrument_id,
		date,
		hedgers_long + swap_long + swap_spread AS commercial_long,
		hedgers_short + swap_short - swap_spread AS commercial_short,
		managed_long + other_long AS noncommercial_long,
		managed_short + other_short AS noncommercial_short,
		managed_spread + other_spread AS noncommercial_spread,
		nonreportable_long,
		nonreportable_short
	FROM cot;
