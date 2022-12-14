<!doctype html>
<html>
	<head>
		<title>preview page</title>
		<style type="text/css">
			<?=$default_preview_css?>
		</style>

		<script type="text/javascript" src="<?=$jquery_src?>"></script>
		<script type='text/javascript'>
			var extraHeight = 0;
			//jshint ignore:start
			var frameCount = <?=count($page_url)?>;
			//jshint ignore:end
			var done = 0;

			// -------------------------------------
			//	This seems archaic but the problem
			//	here is stupid ie8. It doesn't fire
			//	load events for iframes unless
			//	its on the element itself.
			//	The second problem is that its
			//	scrollheight is not counted in its
			//	overallheight for whatever reason
			//	so you get the madness below. But it
			//	works.
			// -------------------------------------

			function frameLoad (thing)
			{
				var that		= thing;
				var $that		= $(that);
				var contents	= $that.contents();
				var htmlHeight	= contents.find('html').outerHeight();
				var body		= contents.find('body');
				var bodyHeight	= body.outerHeight();
				var bodyScrollh	= body.get(0).scrollHeight;
				//get tallest
				var height		= (htmlHeight > bodyHeight ? htmlHeight : bodyHeight);
				//is scrollheight taller still?
				height		= (height > bodyScrollh ? height : bodyScrollh);
				//i didn't want to do this but guess which browser
				//needs it to function correctly? guess... go on.
				body.css('overflow', 'hidden');
				$that.height(height + extraHeight);
				allDone();
			}


			//not all of the iframes are going to finish loading at
			//the same time so we need to wait until they are all finsihed
			function allDone()
			{
				done++;

				if (done == frameCount)
				{
					//we need to make sure out prevention clicker covers all
					var htmlHeight = $('html').outerHeight();
					var bodyHeight = $('body').outerHeight();

					$('.preventy_mcclicker').click(function(e){
						e.preventDefault();
						return false;
					}).height((htmlHeight > bodyHeight ? htmlHeight : bodyHeight) + extraHeight);
				}
			}
		</script>
	</head>
	<body>
		<div class="preventy_mcclicker"></div>
	<?php for ($i = 1, $l = count($page_url); $i <= $l; $i++):?>
			<div class="page_wrapper">
				<h3><?=lang('page')?> <?=($i)?></h2>
				<iframe src="<?=$page_url[$i-1]?>" class="form_holder" onload="frameLoad(this);" frameborder="0"></iframe>
			</div>
	<?php endfor;?>
	</body>
</html>