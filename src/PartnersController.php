<?php

	namespace Egorryaroslavl\Partners;

	use App\Http\Controllers\Controller;
	use Illuminate\Http\Request;
	use Illuminate\Validation\Rule;
	use Egorryaroslavl\Partners\Models\PartnerModel;
	use Intervention\Image\Facades\Image;


	class PartnersController extends Controller
	{


		function messages()
		{
			$strLimit = config( 'admin.settings.text_limit.text_short_description.', 300 );
			return [
				'name.required'         => 'Поле "Имя" обязятельно для заполнения!',
				'alias.required'        => 'Поле "Алиас" обязятельно для заполнения!',
				'name.unique'           => 'Значение поля "Имя" не является уникальным!',
				'alias.unique'          => 'Значение поля "Алиас" не является уникальным!',
				'description.required'  => 'Поле "Текст" обязятельно для заполнения!',
				'short_description.max' => 'Поле "Краткий текст" не должно быть более ' . $strLimit . ' символов!',

			];

		}

		public function index()
		{

			$data        = PartnerModel::orderBy( 'pos', 'ASC' )
				->paginate( config( 'admin.partners.paginate' ) );
			$data->table = 'partners';
			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Партнёры</h2><ol class="breadcrumb"><li><a href="/admin">Главная</a></li><li class="active"><a href="/admin/partners">Партнёры</a></li></ol></div></div>';


			return view( 'partners::index',
				[
					'data'        => $data,
					'breadcrumbs' => $breadcrumbs
				] );


		}


		public function create()
		{
			$data        = new PartnerModel();
			$data->act   = 'partners-store';
			$data->table = 'partners';

			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Партнёры</h2><ol class="breadcrumb"><li><a href="/admin">Главная</a></li><li class="active"><a href="/admin/partners">Партнёры</a></li><li><strong>Создание новой категории</strong></li></ol></div></div>';

			return view( 'partners::form', [ 'data' => $data, 'breadcrumbs' => $breadcrumbs ] );


		}

		public function store( Request $request )
		{

			$v = \Validator::make( $request->all(), [
				'name' => 'required|unique:partners|max:255',
			], $this->messages() );


			if( $v->fails() ){
				return redirect( 'admin/partners/create' )
					->withErrors( $v )
					->withInput();
			}


			$input        = $request->all();
			$input        = array_except( $input, '_token' );
			$PartnerModel = PartnerModel::create( $input );
			$id           = $PartnerModel->id;

			//	dd( [ $id, $input ] );

			/* если в имени файла нет 'upload' значит от ещё в /tmp */
			if( !empty( $request->icon ) && !strpos( $request->icon, 'upload' ) ){

				/* если файла нет, - сообщаем */
				if( !file_exists( $request->icon ) ){
					//	return [ 'error' => 'Файл не неайден!' ];
					return redirect( 'admin/partners/' . $request->id . '/edit' )
						->withErrors( [ 'error' => 'Файл не неайден!' ] )
						->withInput();
				}


				$baseName = basename( $request->icon );


				/* в /tmp файл имеет в имени _token Меняем его не id категории и прибавляем к нему путь для сохранения иконок */
				$fileName = config( 'admin.partners.icons_dir' ) . str_replace( $request->_token, $id, $baseName );
				/* абсоютный путь */
				$filePath = public_path( $fileName );
				/* также делаем для превью */
				$fileNameSmall = config( 'admin.partners.icons_dir' ) . str_replace( $request->_token, $id . '_small', $baseName );
				/* абсоютный путь для превью */
				$filePathSmall = public_path( $fileNameSmall );


				Image::make( $request->icon )
					->save( $filePath )
					->widen( config( 'admin.partners.icon_width' ), function ( $constraint ){
						$constraint->upsize();
					} )
					->heighten( config( 'admin.partners.icon_height' ), function ( $constraint ){
						$constraint->upsize();
					} )->save( $filePathSmall );
				//dd([$fileName,$filePath,$fileNameSmall,$filePathSmall ]);
				/* Теперь обновим поле icon именем с id */
				$category       = PartnerModel::find( $id );
				$category->icon = '/' . $fileNameSmall;
				$category->save();

			}

			\Session::flash( 'message', 'Запись добавлена!' );

			if( isset( $request->submit_button_stay ) ){
				return redirect()->back();
			}
			return redirect( '/admin/partners' );


		}

		public function edit( $id )
		{

			$PartnerModel = PartnerModel::class;
			$data         = $PartnerModel::where( 'id', $id )->first();

			if( is_null( $data ) ){
				return redirect( '/admin/partners' );
			};

			$data->table = 'partners';
			$data->act   = 'partners-update';


			if( !file_exists( public_path( $data->icon ) )
				|| empty( $data->icon )
				|| !isset( $data->icon )
			){
				$data->icon = null;
			}


			$breadcrumbs = '<div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12"><h2>Партнёры</h2><ol 
class="breadcrumb"><li><a href="/admin">Главная</a></li><li 
class="active"><a href="/admin/partners">Партнёры</a></li><li>Редактирование <strong>[
 <a href="/partners/' . $data->alias . '" style="color:blue" title="Смотреть на пользовательской части">' . $data->name . ' <img src="/_admin/img/extlink.png" alt="" 
 style="margin:0"></a> ]</strong></li></ol></div></div>';

			return view( 'partners::form', [
				'data'        => $data,
				'breadcrumbs' => $breadcrumbs,
			] );
		}


		public function update( Request $request )
		{


			/* Определяем куда редиректить после выполнения */
			$direct = isset( $request->submit_button_stay ) ? 'stay' : 'back';
			/* если в имени файла нет 'upload' значит от ещё в /tmp */
			if( !empty( $request->icon ) && !strpos( $request->icon, 'upload' ) ){

				/* если файла нет, - сообщаем */
				if( !file_exists( $request->icon ) ){
					//	return [ 'error' => 'Файл не неайден!' ];
					return redirect( 'admin/partners/' . $request->id . '/edit' )
						->withErrors( [ 'error' => 'Файл не неайден!' ] )
						->withInput();
				}

				$baseName = basename( $request->icon );
				/* в /tmp файл имеет в имени _token Меняем его не id категории и прибавляем к нему путь для сохранения иконок */
				$fileName = config( 'admin.partners.icons_dir' ) . str_replace( $request->_token, $request->id, $baseName );
				/* абсоютный путь */
				$filePath = public_path( $fileName );
				/* также делаем для превью */
				$fileNameSmall = config( 'admin.partners.icons_dir' ) . str_replace( $request->_token, $request->id . '_small', $baseName );
				/* абсоютный путь для превью */
				$filePathSmall = public_path( $fileNameSmall );


				Image::make( $request->icon )
					->save( $filePath )
					->widen( config( 'admin.partners.icon_width' ), function ( $constraint ){
						$constraint->upsize();
					} )
					->heighten( config( 'admin.partners.icon_height' ), function ( $constraint ){
						$constraint->upsize();
					} )->save( $filePathSmall );


			}

			/*  */
			$v = \Validator::make( $request->all(), [
				'name' => [
					'required',
					Rule::unique( 'partners' )->ignore( $request->id ),
					'max:255'
				],

				'alias'       => [
					'required',
					Rule::unique( 'partners' )->ignore( $request->id ),
					'max:255'
				],
				'description' => 'required',


			], $this->messages() );


			/* если есть ошибки - сообщаем об этом */
			if( $v->fails() ){
				return redirect( 'admin/partners/' . $request->id . '/edit' )
					->withErrors( $v )
					->withInput();
			}

			$category              = PartnerModel::find( $request->id );
			$category->name        = $request->name;
			$category->alias       = $request->alias;
			$category->url         = $request->url;
			$category->description = $request->description;
			$category->public      = isset( $request->public ) ? $request->public : 0;
			$category->anons       = isset( $request->anons ) ? $request->anons : 0;
			$category->hit         = isset( $request->hit ) ? $request->hit : 0;
			if( isset( $request->icon ) ){
				$category->icon = '/' . $fileNameSmall;
			};
			$category->h1                  = $request->h1;
			$category->metatag_title       = $request->metatag_title;
			$category->metatag_description = $request->metatag_description;
			$category->metatag_keywords    = $request->metatag_keywords;
			$category->save();

			\Session::flash( 'message', 'Запись обновлена!' );


			if( $direct == 'back' ){
				return redirect( url( '/admin/partners' ) );
			}

			if( $direct == 'stay' ){
				return redirect()->back();
			}


		}


		public function destroy( $id )
		{

			$category      = PartnerModel::find( $id );
			$fileSmallPath = public_path() . $category->icon;
			$filePath      = str_replace( '_small', '', $fileSmallPath );
			if( file_exists( $fileSmallPath ) ){
				unlink( $fileSmallPath );
			}
			if( file_exists( $filePath ) ){
				unlink( $filePath );
			}
			$category->delete();
			return redirect()->back();

		}

		public function translite( Request $request )
		{

			$dictionary = array(
				"А" => "a",
				"Б" => "b",
				"В" => "v",
				"Г" => "g",
				"Д" => "d",
				"Е" => "e",
				"Ж" => "zh",
				"З" => "z",
				"И" => "i",
				"Й" => "y",
				"К" => "K",
				"Л" => "l",
				"М" => "m",
				"Н" => "n",
				"О" => "o",
				"П" => "p",
				"Р" => "r",
				"С" => "s",
				"Т" => "t",
				"У" => "u",
				"Ф" => "f",
				"Х" => "h",
				"Ц" => "ts",
				"Ч" => "ch",
				"Ш" => "sh",
				"Щ" => "sch",
				"Ъ" => "",
				"Ы" => "yi",
				"Ь" => "",
				"Э" => "e",
				"Ю" => "yu",
				"Я" => "ya",
				"а" => "a",
				"б" => "b",
				"в" => "v",
				"г" => "g",
				"д" => "d",
				"е" => "e",
				"ж" => "zh",
				"з" => "z",
				"и" => "i",
				"й" => "y",
				"к" => "k",
				"л" => "l",
				"м" => "m",
				"н" => "n",
				"о" => "o",
				"п" => "p",
				"р" => "r",
				"с" => "s",
				"т" => "t",
				"у" => "u",
				"ф" => "f",
				"х" => "h",
				"ц" => "ts",
				"ч" => "ch",
				"ш" => "sh",
				"щ" => "sch",
				"ъ" => "y",
				"ы" => "y",
				"ь" => "",
				"э" => "e",
				"ю" => "yu",
				"я" => "ya",
				"-" => "_",
				" " => "_",
				"," => "_",
				"." => "_",
				"?" => "",
				"!" => "",
				"«" => "",
				"»" => "",
				":" => "",
				'ё' => "e",
				'Ё' => "e",
				"*" => "",
				"(" => "",
				")" => "",
				"[" => "",
				"]" => "",
				"<" => "",
				">" => ""
			);
			$string     = preg_replace( '/[^\w\s]/u', ' ', $request->alias_source );
			$string     = mb_strtolower( strtr( strip_tags( trim( $string ) ), $dictionary ) );
			$alias      = preg_replace( '/[_]+/', '_', $string );
			return json_encode( [ 'alias' => $alias ] );
		}


		public static function changestatus( Request $request )
		{
			$sql = "
			UPDATE `" . $request->table . "` 
			SET `" . $request->field . "` = NOT `" . $request->field . "` WHERE id =" . $request->id;

			$res = \DB::update( $sql );

			if( $res > 0 ){
				$current = $request->value > 0 ? '0' : '1';
				echo json_encode( [ 'error' => 'ok', 'message' => $current ] );
			} else{
				echo json_encode( [ 'error' => 'error', 'message' => '' ] );
			}

		}

		public function reorder( Request $request )
		{


			if( isset( $request->sort_data ) ){

				$id        = array();
				$table     = $request->table;
				$sort_data = $request->sort_data;

				parse_str( $sort_data );

				$count = count( $id );
				for( $i = 0; $i < $count; $i++ ){
					\DB::update( 'UPDATE `' . $table . '` SET `pos`=' . $i . ' WHERE `id`=? ', [ $id[ $i ] ] );

				}


			}
		}


	}