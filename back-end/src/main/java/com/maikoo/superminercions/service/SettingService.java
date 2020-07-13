package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.SettingDTO;
import com.maikoo.superminercions.model.query.SettingQuery;

public interface SettingService {

    SettingDTO information();

    void update(SettingQuery settingQuery);
}
