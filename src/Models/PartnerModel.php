<?php


	namespace Egorryaroslavl\Partners\Models;

	use Illuminate\Database\Eloquent\Model;


	class PartnerModel extends Model
	{
		protected $table = 'partners';

		protected $fillable = [
			'name',
			'alias',
			'description',
			'url',
			'icon',
			'pos',
			'public',
			'anons',
			'hit',
			'h1',
			'metatag_title',
			'metatag_description',
			'metatag_keywords' ];

		protected $casts = [
			'public'  => 'boolean',
			'anons'   => 'boolean',
			'hit'     => 'boolean',
		];

	}