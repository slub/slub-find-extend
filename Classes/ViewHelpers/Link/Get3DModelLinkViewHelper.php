<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

class Get3DModelLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * Register arguments.
	 * @return void
	 */
  public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('pickupdesc', 'string', 'The pickup location', TRUE);
		$this->registerArgument('mediatype', 'string', 'The type of media', FALSE, "");
	}

	/**
	 * Maps location string to location link in 3d.slub-dresden.de
	 *
	 * @return string
	 */
	public function render() {

		$pickupDesc = $this->arguments['pickupdesc'];
		$pickupID;

		// There are certain media types that have to be handed out at the service desk but whose pickup destination cannot be set more specific than `Zentralbibliothek` in LIBERO
		$mediatype =  $this->arguments['mediatype'];
		switch ($mediatype){
			case "CD":
			case "DVD":
				$pickupDesc = "Zentralbibliothek Servicetheke";
				break;
		}

		switch ($pickupDesc) {
			case "Zentralbibliothek":
				$pickupDesc = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
				$pickupID = 'ol3tb';
				break;
			case "Zentralbibliothek Ebene 0 SB-Regal":
				$pickupDesc = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
				$pickupID = 'ol3tb';
				break;
			case "Zentralbibliothek Ebene -1 SB-Regal Zeitungen":
				$pickupDesc = "Zentralbibliothek<br>Ebene -1<br>SB-Regal Zeitungen";
				$pickupID = 'g36eg';
				break;
			case "Zentralbibliothek Ausleihtheke":
			case "Zentralbibliothek Servicetheke":
				$pickupDesc = "Zentralbibliothek<br>Servicetheke";
				$pickupID = 'qtszs';
				break;
			case "Zentralbibliothek Ebene -1 IP Musik Mediathek":
			case "Zentralbibliothek Ebene -1 IP Musik  Mediathek":
				$pickupDesc = "Zentralbibliothek<br>Ebene -1<br>IP Musik Mediathek";
				$pickupID = 'ga3q0';
				break;
			case "Zentralbibliothek Ebene -1 Lesesaal Sondersammlungen":
				$pickupDesc = "Zentralbibliothek<br>Ebene -1<br>Lesesaal Sondersammlungen";
				$pickupID = '6r9fv';
				break;
			case "Zentralbibliothek Ebene -2 Lesesaal Kartensammlung":
				$pickupDesc = "Zentralbibliothek<br>Ebene -2<br>Lesesaal Kartensammlung";
				$pickupID = '7h0wg';
				break;
			case "ZwB Rechtswissenschaft":
				$pickupDesc = "Zweigbibliothek Rechtswissenschaft";
				$pickupID = '0gryf';
				break;
			case "Bereichsbibliothek Drepunct":
				$pickupID = 'dsm3h';
				break;
			case "ZwB Medizin":
				return "<a href='https://www.slub-dresden.de/ueber-uns/standorte/medizin/'>Zweigbibliothek Medizin</a>";
			case "ZwB Forst":
				return "<a href='https://www.slub-dresden.de/ueber-uns/standorte/forstwesen/'>Zweigbibliothek Forst</a>";
			default:
				return $pickupDesc;
		}

		return "<a href='https://3d.slub-dresden.de/s/" . $pickupID . "'>". $pickupDesc ."</a>";

	}
}

?>
