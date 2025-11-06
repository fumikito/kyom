/**
 * Block Variations
 *
 * @package kyom
 */

wp.domReady( () => {

	// Netabareブロック（Groupブロックのバリエーション）
	wp.blocks.registerBlockVariation( 'core/group', {
		name: 'netabare',
		title: 'ネタバレ',
		description: 'クリックするまで内容がぼかされて表示されるブロックです。',
		category: 'kyom',
		icon: 'hidden',
		attributes: {
			className: 'netabare'
		},
		innerBlocks: [
			[ 'core/paragraph', {placeholder: 'ネタバレの内容を入力...'} ]
		],
		scope: [ 'inserter' ]
	});

	// 注釈ブロック（Groupブロックのバリエーション）
	wp.blocks.registerBlockVariation( 'core/group', {
		name: 'annotation',
		title: '注釈',
		description: '補足説明や注釈を表示するためのブロックです。',
		category: 'kyom',
		icon: 'info',
		attributes: {
			tagName: 'aside',
			className: 'annotation'
		},
		innerBlocks: [
			[ 'core/paragraph', {placeholder: '注釈の内容を入力...'} ]
		],
		scope: [ 'inserter' ]
	});
});
