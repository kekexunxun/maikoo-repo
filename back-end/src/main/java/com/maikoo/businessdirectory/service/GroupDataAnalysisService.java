package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.GroupDTO;

import java.util.List;

public interface GroupDataAnalysisService {
    List<GroupDTO> getGroupData(String sType);

    List<GroupDTO> getGroupUserData(String sType);

}
