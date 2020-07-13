package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.ETHSwapSMCDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

@Mapper
public interface ETHSwapSMCDao {
    @Insert("INSERT " +
            "INTO " +
                "c_order_eth_smc" +
                "(c_order_id, eth_price, smc_price, eth_quantity, smc_quantity, type) " +
            "VALUES" +
                "(#{id}, #{ethPrice}, #{smcPrice}, #{ethQuantity}, #{smcQuantity}, #{type})")
    int insert(ETHSwapSMCDO ethSwapSMCDO);

    @Select("SELECT " +
                "coes.id " +
            "FROM " +
                "c_order co " +
                "JOIN c_order_eth_smc coes ON co.id = coes.c_order_id " +
                "AND co.deleted_at IS NULL " +
            "WHERE " +
                "co.c_user_id = #{customerUserId} " +
                "AND coes.type = #{type}")
    List<Long> selectPageIdsByType(@Param("customerUserId") long customerUserId, @Param("type") int type);

    List<ETHSwapSMCDO> selectByIds(@Param("ids") List<Long> ids);

    ETHSwapSMCDO selectOne(long orderSN);

    List<ETHSwapSMCDO> selectAllWithCustomerByType(@Param("type") int type);
}
