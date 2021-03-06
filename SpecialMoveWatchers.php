<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install MoveWatchers, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MoveWatchers/MoveWatchers.php" );
EOT;
	exit( 1 );
}
class SpecialMoveWatchers extends FormSpecialPage {
	function __construct() {
		parent::__construct( 'MoveWatchers', 'movewatchers' );
		
		if (  !$this->userCanExecute( $this->getUser() )  ) {
			$this->displayRestrictionError();
			return;
		}
	}
	
	/**
     * Override the parent to set where the special page appears on Special:SpecialPages
     * 'other' is the default, so you do not need to override if that's what you want.
     * Specify 'media' to use the <code>specialpages-group-media</code> system interface 
     * message, which translates to 'Media reports and uploads' in English;
     * 
     * @return string
     */
    function getGroupName() {
		return 'users';
    }
	
	protected function getFormFields() {
	
		$request = $this->getRequest();
		
		$formFields = array(
			'movewatchers-moveFrom' => array(
				'label-message' => 'movewatchers-move-from',
				'type' => $request->wasPosted()? 'hidden':'text',
				'required' => true,
				'default' => $request->getText('moveFrom')
			),
			
			'movewatchers-moveTo' => array(
				'label-message' => 'movewatchers-move-to',
				'type' => $request->wasPosted()? 'hidden':'text',
				'required' => true,
				'default' => $request->getText('moveTo')
			),
		);
		
		return( $formFields );
	}
	
	protected function getDisplayFormat() {
		return 'vform';
	}
	
	protected function alterForm( HTMLForm $form ) {
		$form->setWrapperLegend( false );
		
		if( $this->getRequest()->wasPosted() ) {
			$form->suppressDefaultSubmit(true);
		}
			
	}
	
	public function onSubmit( array $data ) {
		$output = $this->getOutput();
		$moveFrom = $data['movewatchers-moveFrom'];
		$moveTo = $data['movewatchers-moveTo'];
		
		if( $moveTo == $moveFrom ) {
			$out = $this->getOutput();
			$out->addHtml("<div class=\"errorbox\">");
			$out->addWikiMsg("movewatchers-duplicate-pages");
			$out->addHtml("</div>");
			
		} else {
			$moveFromTitle = Title::newFromText($data['movewatchers-moveFrom']);
			$moveToTitle = Title::newFromText($data['movewatchers-moveTo']);
			$this->moveWatchers( $moveFromTitle, $moveToTitle );
		}
	}
	
	protected function moveWatchers( $moveFromTitle, $moveToTitle ) {
		$dbr = wfGetDB( DB_SLAVE );
		
		$output = $this->getOutput();
		
		$res = $dbr->select(
			'watchlist', // from
			'wl_user',  // row
			array( // where
				'wl_namespace' => $moveFromTitle->getNamespace(),
				'wl_title' => $moveFromTitle->getDBkey(),
			), 
			__METHOD__ 
		);
		
		$output->addWikiMsg("movewatchers-moving-watching-from", $moveFromTitle->getDBkey(), $moveToTitle->getDBKey());
		$output->addWikiMsg("movewatchers-below-users-changed");
		
		$outText = "";
		   
		foreach ( $res as $row ) {
			$u = User::newFromID( $row->wl_user );
			$u->removeWatch($moveFromTitle, WatchedItem::IGNORE_USER_RIGHTS);
			$u->addWatch($moveToTitle, WatchedItem::IGNORE_USER_RIGHTS);
			$outText .= "*[[User:" . $u->getName() . "]]\n";
		}
		
		$this->logChange( $moveFromTitle, $moveToTitle );
		
		$output->addWikitext($outText);
	}
	
	private function logChange( $moveFromTitle, $moveToTitle ) {
		$logEntry = new ManualLogEntry( 'movewatchers', 'movewatchers' );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $moveFromTitle );
		$logEntry->setParameters( 
			array(
				"4::fromPage" => $moveFromTitle->getDBkey(),
				"5::destPage" => $moveToTitle->getDBkey()
			)
		);
		
		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}
}