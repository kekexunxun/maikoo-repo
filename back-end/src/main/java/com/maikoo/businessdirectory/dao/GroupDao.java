package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.GroupDO;
import org.apache.ibatis.annotations.Param;

import java.util.List;

public interface GroupDao {

    List<GroupDO> selectByUserId(@Param("userId") long userId, @Param("offset") int offset, @Param("perPage") int perPage);
}
