<?php

namespace Slub\SlubFindExtend\ViewHelpers\Link;

/**
 *
 *
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class Get3DModelLinkViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Register arguments.
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pickupdesc', 'string', 'The pickup location', true);
        $this->registerArgument('mediatype', 'string', 'The type of media', false, "");
    }

    /**
     * Maps location string to location link in 3d.slub-dresden.de
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $pickupDesc = $arguments['pickupdesc'];
        $pickupID = '';

        // There are certain media types that have to be handed out at a specific location but whose pickup destination cannot be set more specific in LIBERO
        $mediatype =  $arguments['mediatype'];
        switch ($mediatype) {
            case "CD":
            case "DVD":
                $pickupDesc = "Zentralbibliothek Servicetheke";
                break;
            case "M":
            case "W":
                $pickupDesc = "Makerspace M1 DrePunct";
                break;
        }

        switch ($pickupDesc) {
            case "Zentralbibliothek":
                $pickupDesc = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
                $pickupID = 'eklkb';
                break;
            case "Zentralbibliothek Ebene 0 SB-Regal":
                $pickupDesc = "Zentralbibliothek<br>Ebene 0<br>SB-Regal";
                $pickupID = 'eklkb';
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
            case "Zentralbibliothek Ebene -1 AV-Plätze Digitale Mediathek":
                $pickupDesc = "Zentralbibliothek Ebene -1<br>AV-Plätze Digitale Mediathek";
                $pickupID = '05a8h';
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
                return "<a href='https://www.slub-dresden.de/besuchen/oeffnungszeiten-und-standorte/bibliothek-fiedlerstrasse-medizin'>Zweigbibliothek Medizin</a>";
            case "ZwB Forst":
            case "ZwB Tharandt":
            case "ZwB Forstwissenschaft":
                return "<a href='https://www.slub-dresden.de/besuchen/oeffnungszeiten-und-standorte/bibliothek-tharandt-forstwesen'>Zweigbibliothek Forst</a>";
            case "ZwB Erziehungswissenschaften":
                return "<a href='https://www.slub-dresden.de/besuchen/oeffnungszeiten-und-standorte/bibliothek-august-bebel-strasse-textlab'>Zweigbibliothek Erziehungswissenschaften</a>";
            case "Makerspace M1 DrePunct":
                $pickupID = 'ivn8e';
                break;
            default:
                return $pickupDesc;
        }

        return "<a href='https://3d.slub-dresden.de/s/" . $pickupID . "'>". $pickupDesc ."</a>";
    }
}
