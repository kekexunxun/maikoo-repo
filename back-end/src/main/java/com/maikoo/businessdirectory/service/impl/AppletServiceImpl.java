package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.AppletDao;
import com.maikoo.businessdirectory.model.AppletDO;
import com.maikoo.businessdirectory.model.dto.SettingDTO;
import com.maikoo.businessdirectory.model.query.AppletQuery;
import com.maikoo.businessdirectory.service.AppletService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class AppletServiceImpl implements AppletService {
    @Autowired
    private AppletDao appletDao;
    @Override
    public void appletSetting(AppletQuery appletQuery) {
        AppletDO appletDO = AppletDO.valueOf(appletQuery);
        appletDao.update(appletDO);
    }

    @Override
    public SettingDTO information() {
        return SettingDTO.valueOf(appletDao.select());
    }
}
