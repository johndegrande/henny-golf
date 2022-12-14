<?php if (isset($wrappers) AND $wrappers): ?>{exp:freeform:composer form_id="<?=$form_id?> "}<?php endif;?>
<style type="text/css">
	.ff_composer * {
		-webkit-box-sizing	: border-box;
		-moz-box-sizing		: border-box;
		box-sizing			: border-box;
	}
	.ff_composer .line,
	.ff_composer .line:last-child,
	.ff_composer .last_unit{overflow:hidden;}
	.ff_composer .unit{float:left; padding:10px;}
	.ff_composer .unit_right{float:right;}
	.ff_composer .size1of1{float:none;}
	.ff_composer .size1of2{width:50%;}
	.ff_composer .size1of3{width:33.33333%;}
	.ff_composer .size2of3{width:66.66666%;}
	.ff_composer .size1of4{width:25%;}
	.ff_composer .size3of4{width:75%;}
	.ff_composer .line:last-child,
	.ff_composer .last_unit{float:none;width:auto;}
	.ff_composer p,
	.ff_composer h1,
	.ff_composer h2,
	.ff_composer h3,
	.ff_composer h4,
	.ff_composer h5,
	.ff_composer h6 {margin-top: 0;}
	.ff_composer .required_item {margin-left: 4px; color:red;}
	.ff_composer textarea,
	.ff_composer input[type="text"],
	.ff_composer input[type="email"],
	.ff_composer input[type="url"],
	.ff_composer input[type="number"],
	.ff_composer input[type="password"],
	.ff_composer input[type="search"] {width: 75%;}
	.ff_composer ul.dynamic_recipients {list-style: none; padding:0;}
	.ff_composer .field_label {font-weight: bold;}
</style>
<div class="ff_composer">
{composer:page}
	{composer:rows}
			<div class="line">
		{composer:columns}
				<div class="unit size1of{composer:column_total}">
				{if composer:field_total == 0}
					&nbsp;
				{/if}
			{composer:fields}
				{if composer:field_label}
					{if composer:field_type == 'nonfield_captcha'}
						{if freeform:captcha}
						<p>
							<strong>{composer:field_label}</strong>
						</p>
						{/if}
					{if:else}
						<label class="field_label" {if composer:field_name != ''}for="freeform_{composer:field_name}"{/if}>
							{composer:field_label}{if composer:field_required}<span class="required_item">*</span>{/if}
						</label>
					{/if}
				{/if}
				{if composer:field_output}
					{if composer:field_type == 'nonfield_title'}
						<h2>{composer:field_output}</h2>
					{if:elseif composer:field_type == 'nonfield_paragraph'}
						{composer:field_output}
					{if:elseif composer:field_type == 'nonfield_captcha'}
						{if freeform:captcha}
								{freeform:captcha}<br />
								<input type="text" name="captcha" value="" size="20" maxlength="20" style="width:140px;" />
						{/if}
					{if:else}
						<p>{composer:field_output}</p>
					{/if}
				{/if}
			{/composer:fields}
				</div>
		{/composer:columns}
			</div>
	{/composer:rows}
{/composer:page}
</div>
<?php if (isset($wrappers) AND $wrappers): ?>{/exp:freeform:composer}<?php endif;?>