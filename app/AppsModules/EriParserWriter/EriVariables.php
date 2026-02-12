<?php

namespace Vanguard\AppsModules\EriParserWriter;

use Illuminate\Support\Arr;

class EriVariables
{
    /**
     * @param string $identifier
     * @return string
     */
    public static function getLastFor(string $identifier): string
    {
        return Arr::last(self::getAllFor($identifier)) ?? '';
    }

    /**
     * @param string $identifier
     * @return string[]
     */
    public static function getAllFor(string $identifier): array
    {
        return self::$vars[$identifier] ?? [];
    }

    /**
     * @var array[]
     */
    protected static $vars = [
        'FeedLineRec' => [
            'FeedLineEnabled',
            'FeedLineDatabase',
            'FeedLineDescription',
            'FeedLineClassificationCategory',
            'FeedLineNote',
            'FeedLineNum',
            'FeedLineUseShielding',
            'ExcludeFeedLineFromTorque',
            'FeedLineNumPerRow',
            'FeedLineFace',
            'FeedLineComponentType',
            'FeedLineGroupTreatmentType',
            'FeedLineRoundClusterDia',
            'FeedLineWidth',
            'FeedLinePerimeter',
            'FlatAttachmentEffectiveWidthRatio',
            'AutoCalcFlatAttachmentEffectiveWidthRatio',
            'FeedLineShieldingFactorKaNoIce',
            'FeedLineShieldingFactorKaIce',
            'FeedLineAutoCalcKa',
            'FeedLineCaAaNoIce',
            'FeedLineCaAaIce',
            'FeedLineCaAaIce_1',
            'FeedLineCaAaIce_2',
            'FeedLineCaAaIce_4',
            'FeedLineWtNoIce',
            'FeedLineWtIce',
            'FeedLineWtIce_1',
            'FeedLineWtIce_2',
            'FeedLineWtIce_4',
            'FeedLineFaceOffset',
            'FeedLineOffsetFrac',
            'FeedLinePerimeterOffsetStartFrac',
            'FeedLinePerimeterOffsetEndFrac',
            'FeedLineStartHt',
            'FeedLineEndHt',
            'FeedLineClearSpacing',
            'FeedLineRowClearSpacing'
        ],
        'DishRec' => [
            'DishEnabled',
            'DishDatabase',
            'DishDescription',
            'DishClassificationCategory',
            'DishNote',
            'DishNum',
            'DishFace',
            'DishType',
            'DishOffsetType',
            'DishVertOffset',
            'DishLateralOffset',
            'DishOffsetDist',
            'DishArea',
            'DishAreaIce',
            'DishAreaIce_1',
            'DishAreaIce_2',
            'DishAreaIce_4',
            'DishDiameter',
            'DishWtNoIce',
            'DishWtIce',
            'DishWtIce_1',
            'DishWtIce_2',
            'DishWtIce_4',
            'DishStartHt',
            'DishAzimuthAdjustment',
            'DishBeamWidth'
        ],
        'UserForceRec' => [
            'UserForceEnabled',
            'UserForceDescription',
            'UserForceStartHt',
            'UserForceOffset',
            'UserForceAzimuth',
            'UserForceFxNoIce',
            'UserForceFzNoIce',
            'UserForceAxialNoIce',
            'UserForceShearNoIce',
            'UserForceCaAcNoIce',
            'UserForceFxIce',
            'UserForceFzIce',
            'UserForceAxialIce',
            'UserForceShearIce',
            'UserForceCaAcIce',
            'UserForceFxService',
            'UserForceFzService',
            'UserForceAxialService',
            'UserForceShearService',
            'UserForceCaAcService',
            'UserForceEhx',
            'UserForceEhz',
            'UserForceEv',
            'UserForceEh'
        ],
        'TowerLoadRec' => [
            'TowerLoadEnabled',
            'TowerLoadDatabase',
            'TowerLoadDescription',
            'TowerLoadType',
            'TowerLoadClassificationCategory',
            'TowerLoadNote',
            'TowerLoadNum',
            'TowerLoadFace',
            'TowerOffsetType',
            'TowerOffsetDist',
            'TowerVertOffset',
            'TowerLateralOffset',
            'TowerAzimuthAdjustment',
            'TowerAppurtSymbol',
            'TowerLoadShieldingFactorKaNoIce',
            'TowerLoadShieldingFactorKaIce',
            'TowerLoadAutoCalcKa',
            'TowerLoadCaAaNoIce',
            'TowerLoadCaAaIce',
            'TowerLoadCaAaIce_1',
            'TowerLoadCaAaIce_2',
            'TowerLoadCaAaIce_4',
            'TowerLoadCaAaNoIce_Side',
            'TowerLoadCaAaIce_Side',
            'TowerLoadCaAaIce_Side_1',
            'TowerLoadCaAaIce_Side_2',
            'TowerLoadCaAaIce_Side_4',
            'TowerLoadWtNoIce',
            'TowerLoadWtIce',
            'TowerLoadWtIce_1',
            'TowerLoadWtIce_2',
            'TowerLoadWtIce_4',
            'TowerLoadStartHt',
            'TowerLoadEndHt'
        ],
    ];
}