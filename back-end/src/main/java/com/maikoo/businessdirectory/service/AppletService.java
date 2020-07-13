package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.SettingDTO;
import com.maikoo.businessdirectory.model.query.AppletQuery;

public interface AppletService {
    void appletSetting(AppletQuery appletQuery);

    SettingDTO information();
}
