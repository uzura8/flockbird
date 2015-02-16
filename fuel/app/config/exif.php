<?php

return array(
	'filterTags' => array(
		'isEnabled' => false,
		'accepted' => array(
			'ImageWidth',// 画像の幅
			'ImageLength',// 画像の高さ
			'BitsPerSample',// 画像のビットの深さ
			'Compression',// 圧縮の種類
			'PhotometricInterpretation',// 画素構成
			'Make',// 画像入力機器のメーカー名
			'Model',// 画像入力機器のモデル名
			'Orientation',// 画像方向
			'SamplesPerPixel',// コンポーネント数
			'RowsPerStrip',// 1ストリップあたりの行の数
			'StripByteCounts',// ストリップの総バイト数
			'XResolution',// 画像の幅の解像度
			'YResolution',// 画像の高さの解像度
			'PlanarConfiguration',// 画像データの並び
			'ResolutionUnit',// 画像の幅と高さの解像度の単位
			'TransferFunction',// 再生階調カーブ特性
			'Software',// ソフトウェア
			'DateTime',// ファイル変更日時
			'WhitePoint',// 参照白色点の色度座標値
			'PrimaryChromaticities',// 原色の色度座標値
			'JPEGInterchangeFormat',// JPEGのSOIへのオフセット
			'JPEGInterchangeFormatLength',// JPEGデータのバイト数
			'YCbCrCoefficients',// 色変換マトリクス係数
			'YCbCrSubSampling',// YCCの画素構成(Cの間引き率)
			'YCbCrPositioning',// YCCの画素構成(YとCの位置)
			'ReferenceBlackWhite',// 参照黒色点値と参照白色点値
			'Exif IFD Pointer',// Exifタグ
			'GPSInfo IFD Pointer',// GPSタグ
			'ExposureTime',// 露出時間
			'FNumber',// Fナンバー
			'ExposureProgram',// 露出プログラム
			'SpectralSensitivity',// スペクトル感度
			'PhotographicSensitivity',// 撮影感度
			'OECF',// 光電変換関数
			'SensitivityType',// 感度種別
			'StandardOutputSensitivity',// 標準出力感度
			'RecommendedExposureIndex',// 推奨露光指数
			'ISOSpeed',// ISOスピード
			'ISOSpeedLatitudeyyy',// ISOスピードラチチュードyyy
			'ISOSpeedLatitudezzz',// ISOスピードラチチュードzzz
			'ExifVersion',// Exifバージョン
			'DateTimeOriginal',// 原画像データの生成日時
			'DateTimeDigitized',// デジタルデータの作成日時
			'ComponentsConfiguration',// 各コンポーネントの意味
			'CompressedBitsPerPixel',// 画像圧縮モード
			'ShutterSpeedValue',// シャッタースピード
			'ApertureValue',// 絞り値
			'BrightnessValue',// 輝度値
			'ExposureBiasValue',// 露光補正値
			'MaxApertureValue',// レンズ最小Ｆ値
			'SubjectDistance',// 被写体距離
			'MeteringMode',// 測光方式
			'LightSource',// 光源
			'Flash',// フラッシュ
			'FocalLength',// レンズ焦点距離
			'SubjectArea',// 被写体領域
			'FlashpixVersion',// 対応フラッシュピックスバージョン
			'ColorSpace',// 色空間情報
			'PixelXDimension',// 実効画像幅
			'PixelYDimension',// 実効画像高さ
			'FlashEnergy',// フラッシュ強度
			'SpatialFrequencyResponse',// 空間周波数応答
			'FocalPlaneXResolution',// 焦点面の幅の解像度
			'FocalPlaneYResolution',// 焦点面の高さの解像度
			'FocalPlaneResolutionUnit',// 焦点面解像度単位
			'SubjectLocation',// 被写体位置
			'ExposureIndex',// 露出インデックス
			'SensingMethod',// センサ方式
			'FileSource',// ファイルソース
			'SceneType',// シーンタイプ
			'CFAPattern',// CFAパターン
			'CustomRendered',// 個別画像処理
			'ExposureMode',// 露出モード
			'WhiteBalance',// ホワイトバランス
			'DigitalZoomRatio',// デジタルズーム倍率
			'FocalLengthIn35mmFilm',// 35mm換算レンズ焦点距離
			'SceneCaptureType',// 撮影シーンタイプ
			'GainControl',// ゲイン制御
			'Contrast',// 撮影コントラスト
			'Saturation',// 撮影彩度
			'Sharpness',// 撮影シャープネス
			'SubjectDistanceRange',// 被写体距離レンジ
			'BodySerialNumber',// カメラシリアル番号
			'LensSpecification',// レンズの仕様情報
			'LensMake',// レンズのメーカ名
			'LensModel',// レンズのモデル名
			'LensSerialNumber',// レンズシリアル番号
			'Gamma',// 再生ガンマ
			'GPSVersionID',// GPSタグのバージョン
			'GPSLatitudeRef',// 北緯(N) or 南緯(S)
			'GPSLatitude',// 緯度(数値)
			'GPSLongitudeRef',// 東経(E) or 西経(W)
			'GPSLongitude',// 経度(数値)
			'GPSAltitudeRef',// 高度の基準
			'GPSAltitude',// 高度(数値)
			'GPSTimeStamp',// GPS時間(原子時計の時間)
			'GPSSatellites',// 測位に使った衛星信号
			'GPSStatus',// GPS受信機の状態
			'GPSMeasureMode',// GPSの測位方法
			'GPSDOP',// 測位の信頼性
			'GPSSpeedRef',// 速度の単位
			'GPSSpeed',// 速度(数値)
			'GPSTrackRef',// 進行方向の単位
			'GPSTrack',// 進行方向(数値)
			'GPSImgDirectionRef',// 撮影した画像の方向の単位
			'GPSImgDirection',// 撮影した画像の方向(数値)
			'GPSMapDatum',// 測位に用いた地図データ
			'GPSDestLatitudeRef',// 目的地の北緯(N) or 南緯(S)
			'GPSDestLatitude',// 目的地の緯度(数値)
			'GPSDestLongitudeRef',// 目的地の東経(E) or 西経(W)
			'GPSDestLongitude',// 目的地の経度(数値)
			'GPSDestBearingRef',// 目的地の方角の単位
			'GPSDestBearing',// 目的の方角(数値)
			'GPSDestDistanceRef',// 目的地までの距離の単位
			'GPSDestDistance',// 目的地までの距離(数値)
			'GPSProcessingMethod',// 測位方式の名称
			'GPSAreaInformation',// 測位地点の名称
			'GPSDateStamp',// GPS日付
			'GPSDifferential',// GPS補正測位
			'GPSHPositioningError',// 水平方向測位誤差
		),
	),
);

