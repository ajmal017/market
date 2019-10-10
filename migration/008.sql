UPDATE exchange	SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'ICE FUTURES U.S.'),
	main_exchange_code = 'ICE'
	WHERE name IN (
		'ICE FUTURES U.S.',
		'ICE FUTURES ENERGY DIV'
	);
