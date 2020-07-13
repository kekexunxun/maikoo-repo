package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.dto.ETHSellDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

@Mapper
public interface ETHSellDao {
    @Insert("INSERT " +
            "INTO " +
                "c_order_eth" +
                "(c_order_id, current_price, price, quantity) " +
            "VALUES" +
                "(#{id}, #{currentPrice}, #{price}, #{quantity})")
    int insert(ETHSellDO ethSellDO);

    @Select("SELECT " +
                "coe.id " +
            "FROM " +
                "c_order co " +
                "JOIN c_order_eth coe ON co.id = coe.c_order_id " +
                "AND co.deleted_at IS NULL " +
            "WHERE " +
                "co.c_user_id = #{customerUserId}")
    List<Long> selectPageIds(@Param("customerUserId") long customerUserId);

    List<ETHSellDO> selectByIds(@Param("ids") List<Long> ids);

    ETHSellDO selectOne(long orderSN);

    List<ETHSellDO> selectAllWithCustomer();
}
