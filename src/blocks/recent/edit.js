import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ComboboxControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { postList } from '@wordpress/icons';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { title, category, tag } = attributes;

	const blockProps = useBlockProps();

	// カテゴリ・タグの一覧を取得（編集画面の選択肢として使う）。
	const { categories, tags } = useSelect( ( select ) => {
		const query = { per_page: -1, _fields: 'id,name,slug' };
		return {
			categories: select( coreStore ).getEntityRecords( 'taxonomy', 'category', query ),
			tags: select( coreStore ).getEntityRecords( 'taxonomy', 'post_tag', query ),
		};
	}, [] );

	// カテゴリは ID 指定（既存の WP_Query 'cat' と後方互換）。
	const categoryOptions = ( categories || [] ).map( ( term ) => ( {
		value: String( term.id ),
		label: term.name,
	} ) );
	// タグはスラッグ指定（ターム ID は DB 間でズレるため）。
	const tagOptions = ( tags || [] ).map( ( term ) => ( {
		value: term.slug,
		label: term.name,
	} ) );

	const selectedCategory = categoryOptions.find( ( opt ) => opt.value === String( category ) );
	const selectedTag = tagOptions.find( ( opt ) => opt.value === tag );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'kyom' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Title', 'kyom' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						help={ __( 'Leave empty for default title "Recent Posts"', 'kyom' ) }
					/>
					<ComboboxControl
						label={ __( 'Category', 'kyom' ) }
						value={ String( category ) }
						options={ categoryOptions }
						onChange={ ( value ) => setAttributes( { category: value || '' } ) }
						help={ __( 'Filter posts by category (optional).', 'kyom' ) }
						allowReset
					/>
					<ComboboxControl
						label={ __( 'Tag', 'kyom' ) }
						value={ tag }
						options={ tagOptions }
						onChange={ ( value ) => setAttributes( { tag: value || '' } ) }
						help={ __( 'Filter posts by tag (optional).', 'kyom' ) }
						allowReset
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Placeholder
					icon={ postList }
					label={ title || __( 'Recent Posts', 'kyom' ) }
					instructions={ __( 'Displays recent posts in a grid layout. Configure settings in the sidebar.', 'kyom' ) }
				>
					{ selectedCategory && <p>{ __( 'Category:', 'kyom' ) } { selectedCategory.label }</p> }
					{ selectedTag && <p>{ __( 'Tag:', 'kyom' ) } { selectedTag.label }</p> }
				</Placeholder>
			</div>
		</>
	);
}
