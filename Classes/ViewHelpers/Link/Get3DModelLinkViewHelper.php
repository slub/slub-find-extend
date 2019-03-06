<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

class Get3DModelLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Maps location string to location link in 3d.slub-dresden.de
	 *
	 * @param string $content
	 * @return string
	 */

	public function render($content = NULL) {

		if ($content === NULL) {
			$content = $this->renderChildren();
		}

		$pickupID;

		switch ($content) {
			case "Zentralbibliothek":
				$content = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
				$pickupID = '2052';
				break;
			case "Zentralbibliothek Ebene 0 SB-Regal":
				$content = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
				$pickupID = '2052';
				break;
			case "Zentralbibliothek Ebene -1 SB-Regal Zeitungen":
				$content = "Zentralbibliothek<br>Ebene -1<br>SB-Regal Zeitungen";
				$pickupID = '3811';
				break;
			case "Zentralbibliothek Ausleihtheke":
			case "Zentralbibliothek Servicetheke":
				$content = "Zentralbibliothek<br>Servicetheke";
				$pickupID = '1429';
				break;
			case "Zentralbibliothek Ebene -1 IP Musik Mediathek":
			case "Zentralbibliothek Ebene -1 IP Musik  Mediathek":
				$content = "Zentralbibliothek<br>Ebene -1<br>IP Musik Mediathek";
				$pickupID = '2080';
				break;
			case "Zentralbibliothek Ebene -1 Lesesaal Sondersammlungen":
				$content = "Zentralbibliothek<br>Ebene -1<br>Lesesaal Sondersammlungen";
				$pickupID = '2084';
				break;
			case "Zentralbibliothek IP Zeitschriften":
				$content = "Zentralbibliothek<br>IP Zeitschriften";
				$pickupID = '3023';
				break;
			case "Zentralbibliothek Ebene -2 Lesesaal Kartensammlung":
				$content = "Zentralbibliothek<br>Ebene -2<br>Lesesaal Kartensammlung";
				$pickupID = '3021';
				break;
			case "ZwB Rechtswissenschaft":
				$content = "Zweigbibliothek Rechtswissenschaft";
				$pickupID = '3422';
				break;
			case "Bereichsbibliothek Drepunct":
				$pickupID = '3154';
				break;
			case "ZwB Medizin":
				return "<a href='https://www.slub-dresden.de/ueber-uns/standorte/medizin/'>Zweigbibliothek Medizin</a>";
			case "ZwB Forst":
				return "<a href='https://www.slub-dresden.de/ueber-uns/standorte/forstwesen/'>Zweigbibliothek Forst</a>";
			default:
				return $content;
		}

		return "<a href='https://3d.slub-dresden.de/viewer?language=de&project_id=3&activate_location=" . $pickupID . "'>". $content ."</a>";

	}
}

?>
