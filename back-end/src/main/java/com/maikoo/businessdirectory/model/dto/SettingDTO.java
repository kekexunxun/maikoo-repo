package com.maikoo.businessdirectory.model.dto;

import com.maikoo.businessdirectory.model.SettingDO;
import lombok.Data;

@Data
public class SettingDTO {
    private String shareText;
    private String miniName;
    private String lbsKey;

    public static SettingDTO valueOf(SettingDO settingDO) {
        SettingDTO settingDTO = new SettingDTO();
        settingDTO.setShareText(settingDO.getShareText());
        settingDTO.setMiniName(settingDO.getMiniName());
        settingDTO.setLbsKey(settingDO.getLbsKey());
        return settingDTO;
    }
}
