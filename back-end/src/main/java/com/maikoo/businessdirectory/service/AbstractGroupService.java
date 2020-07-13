package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.dao.*;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.util.ArrayList;
import java.util.List;

@Service
public abstract class AbstractGroupService implements GroupService {
    @Autowired
    private ClassGroupDao classGroupDao;
    @Autowired
    private SchoolGroupDao schoolGroupDao;
    @Autowired
    private CommunityGroupDao communityGroupDao;
    @Autowired
    private CountryGroupDao countryGroupDao;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private GroupDao groupDao;
    @Autowired
    private HttpSession session;

    @Override
    public List<GroupDTO> selectByKey(String key) {
        List<GroupDTO> groupDTOList = new ArrayList<>();

        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        List<Long> schoolIds = schoolGroupDao.selectIdsByKeyAndUserId(key, currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(schoolIds)) {
            List<SchoolGroupDO> schoolGroupDOList = schoolGroupDao.selectByIds(schoolIds);
            schoolGroupDOList.forEach(schoolGroupDO -> groupDTOList.add(GroupDTO.valueOf(schoolGroupDO)));
        }

        List<Long> classIds = classGroupDao.selectIdsByKeyAndUserId(key, currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(classIds)) {
            List<ClassGroupDO> classGroupDOList = classGroupDao.selectByIds(classIds);
            classGroupDOList.forEach(classGroupDO -> groupDTOList.add(GroupDTO.valueOf(classGroupDO)));
        }

        List<Long> communityIds = communityGroupDao.selectIdsByKeyAndUserId(key, currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(communityIds)) {
            List<CommunityGroupDO> communityGroupDOList = communityGroupDao.selectByIds(communityIds);
            communityGroupDOList.forEach(communityGroupDO -> groupDTOList.add(GroupDTO.valueOf(communityGroupDO)));
        }

        List<Long> countryIds = countryGroupDao.selectIdsByKeyAndUserId(key, currentUserDO.getUserId());
        if (!CollectionUtils.isEmpty(countryIds)) {
            List<CountryGroupDO> countryGroupDOList = countryGroupDao.selectByIds(countryIds);
            countryGroupDOList.forEach(countryGroupDO -> groupDTOList.add(GroupDTO.valueOf(countryGroupDO)));
        }

        return groupDTOList;
    }

    @Override
    public List<GroupDTO> selectAll(int pageNumber) {
        String key = null;
        pageNumber = (pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber()) - 1;
        int perPage = customEnvironmentConfig.getPerPage();
        int offset = pageNumber * perPage;

        List<GroupDTO> groupDTOList = new ArrayList<>();

        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");
        List<GroupDO> groupDOList = groupDao.selectByUserId(currentUserDO.getUserId(), offset, perPage);
        if (!CollectionUtils.isEmpty(groupDOList)) {
            groupDOList.forEach(groupDO -> groupDTOList.add(GroupDTO.valueOf(groupDO)));
        }
        return groupDTOList;
    }
}
