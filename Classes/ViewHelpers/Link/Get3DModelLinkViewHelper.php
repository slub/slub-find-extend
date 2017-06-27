<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

class Get3DModelLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @return string
	 */
	public function render($content = NULL) {

		if ($content === NULL) {
			$content = $this->renderChildren();
		}

		$pickupID;

		switch ($content) {
			case "Zentralbibliothek":
				$pickupID = '2052';
				break;
			case "Zentralbibliothek Ebene 0 SB-Regal":
				$pickupID = '2052';
				break;
			case "Zentralbibliothek Ebene -1 SB-Regal Zeitungen":
				$pickupID = '3811';
				break;
			case "Zentralbibliothek Ausleihtheke":
				$pickupID = '1429';
				break;
			case "Zentralbibliothek Ebene -1 IP Musik / Mediathek":
				$pickupID = '2080';
				break;
			case "Zentralbibliothek Ebene -1 Lesesaal Sondersammlungen":
				$pickupID = '2084';
				break;
			case "Zentralbibliothek IP Zeitschriften":
				$pickupID = '3023';
				break;
			case "Zentralbibliothek Ebene -2 Lesesaal Kartensammlung":
				$pickupID = '3021';
				break;
			case "ZwB Rechtswissenschaft":
				$pickupID = '3422';
				break;
			case "Bereichsbibliothek Drepunct":
				$pickupID = '3154';
				break;
			default:
				return $content;
		}

		return "<a href='http://3d.slub-dresden.de/viewer?language=de&project_id=3&activate_location=" . $pickupID . "'>". $content ."</a>";

	}
}

?>