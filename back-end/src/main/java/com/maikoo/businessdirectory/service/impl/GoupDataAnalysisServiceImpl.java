package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.ClassGroupDao;
import com.maikoo.businessdirectory.dao.CommunityGroupDao;
import com.maikoo.businessdirectory.dao.CountryGroupDao;
import com.maikoo.businessdirectory.dao.SchoolGroupDao;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.TimeFrequentQuery;
import com.maikoo.businessdirectory.service.GroupDataAnalysisService;
import com.maikoo.businessdirectory.util.CannedTimeFormat;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.servlet.http.HttpSession;
import java.util.ArrayList;
import java.util.List;

@Service
public class GoupDataAnalysisServiceImpl implements GroupDataAnalysisService {

    @Autowired
    private ClassGroupDao classGroupDao;
    @Autowired
    private CommunityGroupDao communityGroupDao;
    @Autowired
    private SchoolGroupDao schoolGroupDao;
    @Autowired
    private CountryGroupDao CountryGroupDao;

    @Autowired
    private HttpSession session;


    @Override
    public List<GroupDTO> getGroupData(String sType) {
        List<GroupDTO> groupDTOList = new ArrayList<>();
        List<TimeFrequentQuery> list = null;
        if (sType.equalsIgnoreCase("MONTH")) {
            list = CannedTimeFormat.timeFrequent(1);
        } else if (sType.equalsIgnoreCase("YEAR")) {
            list = CannedTimeFormat.timeFrequent(2);
        }
        List<Integer> classIntegerList = new ArrayList<>();
        List<Integer> countryIntegerList = new ArrayList<>();
        List<Integer> communityIntegerList = new ArrayList<>();
        List<Integer> schoolIntegerList = new ArrayList<>();

        GroupDTO classGroupDTO = new GroupDTO();
        GroupDTO countryGroupDTO = new GroupDTO();
        GroupDTO communityGroupDTO = new GroupDTO();
        GroupDTO schoolGroupDTO = new GroupDTO();
        classGroupDTO.setName("同班群");
        countryGroupDTO.setName("同乡群");
        communityGroupDTO.setName("社区群");
        schoolGroupDTO.setName("校友群");
        for (int i = list.size() - 1; i >= 0; i--) {
            int classTemp = classGroupDao.analysisGroupData(list.get(i));
            int countryTemp = CountryGroupDao.analysisGroupData(list.get(i));
            int communityTemp = communityGroupDao.analysisGroupData(list.get(i));
            int schoolTemp = schoolGroupDao.analysisGroupData(list.get(i));

            classIntegerList.add(classTemp);
            countryIntegerList.add(countryTemp);
            communityIntegerList.add(communityTemp);
            schoolIntegerList.add(schoolTemp);
        }
        classGroupDTO.setData(classIntegerList);
        countryGroupDTO.setData(countryIntegerList);
        communityGroupDTO.setData(communityIntegerList);
        schoolGroupDTO.setData(schoolIntegerList);

        groupDTOList.add(classGroupDTO);
        groupDTOList.add(countryGroupDTO);
        groupDTOList.add(communityGroupDTO);
        groupDTOList.add(schoolGroupDTO);

        return groupDTOList;
    }

    @Override
    public List<GroupDTO> getGroupUserData(String sType) {
        List<GroupDTO> groupDTOList = new ArrayList<>();
        List<TimeFrequentQuery> list = null;
        if (sType.equalsIgnoreCase("MONTH")) {
            list = CannedTimeFormat.timeFrequent(1);
        } else if (sType.equalsIgnoreCase("year")) {
            list = CannedTimeFormat.timeFrequent(2);
        }
        List<Integer> classIntegerList = new ArrayList<>();
        List<Integer> countryIntegerList = new ArrayList<>();
        List<Integer> communityIntegerList = new ArrayList<>();
        List<Integer> schoolIntegerList = new ArrayList<>();

        GroupDTO classGroupDTO = new GroupDTO();
        GroupDTO countryGroupDTO = new GroupDTO();
        GroupDTO communityGroupDTO = new GroupDTO();
        GroupDTO schoolGroupDTO = new GroupDTO();

        classGroupDTO.setName("同班群");
        countryGroupDTO.setName("同乡群");
        communityGroupDTO.setName("社区群");
        schoolGroupDTO.setName("校友群");

        for (int i = list.size() - 1; i >= 0; i--) {
            int classTemp = classGroupDao.analysisGroupUserData(list.get(i));
            int countryTemp = CountryGroupDao.analysisGroupUserData(list.get(i));
            int communityTemp = communityGroupDao.analysisGroupUserData(list.get(i));
            int schoolTemp = schoolGroupDao.analysisGroupUserData(list.get(i));

            classIntegerList.add(classTemp);
            countryIntegerList.add(countryTemp);
            communityIntegerList.add(communityTemp);
            schoolIntegerList.add(schoolTemp);
        }
        classGroupDTO.setData(classIntegerList);
        countryGroupDTO.setData(countryIntegerList);
        communityGroupDTO.setData(communityIntegerList);
        schoolGroupDTO.setData(schoolIntegerList);

        groupDTOList.add(classGroupDTO);
        groupDTOList.add(countryGroupDTO);
        groupDTOList.add(communityGroupDTO);
        groupDTOList.add(schoolGroupDTO);

        return groupDTOList;
    }
}
