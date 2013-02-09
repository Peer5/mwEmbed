<?php 
require_once( realpath( dirname( __FILE__ ) )  . '/doc-config.php' );

$featureList = include( 'featureList.php' );

$fullFeaturePath = htmlspecialchars( $_REQUEST['path'] );
$featureParts = explode('/',  $fullFeaturePath);
// init vars: 
$featureCategoryKey = $featureSetKey = $featureFileKey = null;
// parse path: 
$featureCategoryKey = $featureParts[0];
if( isset( $featureParts[1] ) ){
	$featureSetKey = $featureParts[1] ;
}
if( isset( $featureParts[2]) ){
	$featureFileKey = 	$featureParts[2];
}
// TODO check for old path types ( no feature category ) 


// Check for only "$featureCategoryKey" 
if( $featureCategoryKey && isset( $featureList[ $featureCategoryKey ] )
	 && !$featureSetKey &&!$featureFileKey 
){
	// output feature category page: 
	$featureCategory = $featureList[ $featureCategoryKey ];
	?>
	<div class="hero-unit">
			<div class="tagline" >
				<h1><?php echo $featureCategory['title']?></h1>
				<p><?php echo $featureCategory['desc'] ?><p>
			</div>
			<div class="player-container">
			 	<!--  maintain 16/9 aspect ratio: -->
			 	<div id="dummy" style="margin-top: 56.25%;"></div>
			 	<div class="player-container-absolute">
					<div id="kaltura_player" style="width:100%;height:100%"></div>
				</div>
			</div>
			<script>
				kWidget.embed({
					'targetId' : 'kaltura_player',
					'wid' : '_243342',
					'uiconf_id' : '2877502',
					'entry_id' : '1_zm1lgs13'
				});
			</script>
	</div>
	<?php 
	$twoPerRow =0;
	foreach($featureCategory['featureSets'] as $featureSetKey => $featureSet){
		if( $twoPerRow == 0 ){
			?><div class="row-fluid"><?php 
		}	
		// output spans: 
		?>
		<div class="span6">
			<a href="index.php?path=<?php echo $featureCategoryKey . '/' . $featureSetKey ?>">
				<h2><i style="margin-top:7px;margin-right:4px;" class="kicon-<?php echo $featureCategoryKey?>"></i><?php echo $featureSet['title'] ?></h2>
			</a>
			<p><?php echo $featureSet['desc']  ?></p>
			<ul>
				<?php foreach( $featureSet['testfiles'] as $featureFileKey => $featureFile ){
					?><li><a href="index.php?path=<?php echo $featureCategoryKey . '/' . $featureSetKey . '/' . $featureFileKey ?>">
						<?php echo $featureFile['title'] ?></a>
					</li><?php 
				}?>
			</ul>
		</div>
		<?php 
		
		if( $twoPerRow == 0 ){
			?><div><?php 
		}
		$twoPerRow+1;
		if( $twoPerRow == 2 ){
			$twoPerRow =0;
		}
	}
	exit();
} 


// Check for only "$featureCategoryKey/featureSet" 
if( $featureCategoryKey && isset( $featureList[ $featureCategoryKey ] )
	 && $featureSetKey && isset($featureList[ $featureCategoryKey ]['featureSets'][$featureSetKey]  )
	 && !$featureFileKey 
){
	// for now just output the first feature in that category: 
	$featureFileKey = key( $featureList[ $featureCategoryKey ]['featureSets'][$featureSetKey]['testfiles'] );
}

// Output an actual feature: 
if( ! isset( $featureList[ $featureCategoryKey ]['featureSets'][$featureSetKey]['testfiles'][$featureFileKey] ) ){
	echo "feature set path ". $featureKey . " not found "; 
	return ;
} else{
	$feature = $featureList[ $featureCategoryKey ]['featureSets'][$featureSetKey]['testfiles'][$featureFileKey];
}
// Output the title: 
?>
<h2 id="hps-<?php echo $fullFeaturePath; ?>"><?php echo $feature['title'] ?></h2>
<script>
	var iframeLoadCount =0; 
	window['handleLoadedIframe'] = function( id ){
		$('#loading_' + id ).remove();
		iframeLoadCount++;
		doSync = true;
		
		if( iframeLoadCount == 1){
			// done loading get correct offset for hash
			var aNode = $('body').find('a[name="' + location.hash.replace('#', '') +'"]')[0];
			if( aNode ){
				aNode.scrollIntoView();
			}
		}
	}
	var doSync = false;
	function sycnIframeContentHeight(){
		doSync = true;
	}
	setInterval( function(){
		if( doSync ){
			//doSync = false;
			$('iframe').each(function(){
				try{
					$( this ).css(
						'height', 
						$( $( this )[0].contentWindow.document ).height()
					)
				} catch ( e) {
					// could not set iframe height
				}
			});
		}
	}, 250 );
</script>
<?php 
function outputFeatureIframe($featureFileKey, $testFile){
	$iframeId = 'ifid_' . $featureFileKey;
	?>
	<br>
	<a id="a_<?php echo $iframeId ?>"  name="<?php echo $featureFileKey ?>" href="../modules/<?php echo  $testFile['path']; ?>" target="_new" >
		<span style="text-transform: lowercase; padding-top: 50px; margin-top: -50px;font-size:x-small"> <?php echo $testFile['title'] ?> test page >>> </span>
	</a>
	<br>
	<iframe seamless allowfullscreen webkitallowfullscreen mozAllowFullScreen style="border:none;width:100%;height:0px" 
		id="<?php echo $iframeId ?>" 
		onload="handleLoadedIframe('<?php echo $iframeId ?>')" 
		src="">
	</iframe>
	<script>
		var testPath = kDocGetBasePath() + '../modules/<?php echo $testFile['path'] ?>';
		$('#<?php echo $iframeId ?>' ).attr('src', testPath);
		$('#a_<?php echo $iframeId ?>').attr('href', testPath);
	</script>
	<span id="loading_<?php echo $iframeId ?>">Loading <?php echo $featureFileKey?><span class="blink">...</span> </span> 
	<?php 
}

	
// output the features for that path: 
outputFeatureIframe( $featureFileKey, $feature );
