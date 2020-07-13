package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.SMCLockCycleDO;
import com.maikoo.superminercions.model.SMCLockDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface SMCLockDao {
    @ResultMap("lockCycleDOResultMap")
    @Select("SELECT id, cycle, reward FROM comm_lock_cycle")
    List<SMCLockCycleDO> selectAllLockCycle();

    @Update("UPDATE comm_lock_cycle SET cycle = #{cycle}, reward = #{reward} WHERE id = #{id}")
    int updateLockCycle(SMCLockCycleDO smcLockCycleDO);

    @Insert("INSERT " +
            "INTO " +
                "c_order_lock" +
                "(c_order_id, quantity, comm_lock_cycle_id) " +
            "VALUES" +
                "(#{id}, #{quantity}, #{smcLockCycleDO.id})")
    int insert(SMCLockDO smcLockDO);

    @Select("SELECT " +
                "col.id " +
            "FROM " +
                "c_order co " +
                "JOIN c_order_lock col ON co.id = col.c_order_id " +
                "AND co.deleted_at IS NULL " +
            "WHERE " +
                "co.c_user_id = #{customerUserId}")
    List<Long> selectPageIds(long customerUserId);

    List<SMCLockDO> selectByIds(@Param("ids") List<Long> ids);

    List<SMCLockDO> selectAllWithCustomer();

    SMCLockDO selectOne(long orderSN);

    List<SMCLockDO> selectExceedLockCycleByOrderStatus(long timestamp);
}
