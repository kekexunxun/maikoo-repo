package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.PostDTO;
import com.maikoo.businessdirectory.model.query.GroupInformationQuery;
import com.maikoo.businessdirectory.model.query.GroupQuery;

import java.util.List;

public interface GroupService {
    GroupDTO insert(GroupInformationQuery groupInformationQuery);

    void update(GroupInformationQuery groupInformationQuery);

    void remove(long id);

    void changeOwner(GroupQuery groupQuery);

    PostDTO sharePost(long id);

    GroupDTO information(long id);

    List<GroupDTO> selectByKey(String key);

    List<GroupDTO> selectAll(int pageNumber);

    GroupDTO informationByAdmin(long id);

    List<GroupDTO> selectAllByAdmin();

    FileDTO excel();
}
